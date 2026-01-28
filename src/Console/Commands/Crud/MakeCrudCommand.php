<?php

namespace Marufsharia\Hyro\Console\Commands\Crud;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeCrudCommand extends Command
{
    protected $signature = 'hyro:make-crud
                            {name : The name of the CRUD resource (e.g., Post, Product)}
                            {--model= : The model class to use}
                            {--fields= : Comma-separated list of fields (e.g., title:string:required,body:text)}
                            {--searchable= : Comma-separated list of searchable fields}
                            {--sortable= : Comma-separated list of sortable fields}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate a complete Livewire CRUD component with views';

    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->option('model') ?: "App\\Models\\{$name}";
        $fields = $this->parseFields($this->option('fields'));
        $searchable = $this->parseList($this->option('searchable'));
        $sortable = $this->parseList($this->option('sortable'));

        if (empty($fields)) {
            $this->error('Please provide fields using --fields option');
            $this->newLine();
            $this->info('Example: --fields="title:string:required,body:text:nullable,status:select:required"');
            return 1;
        }

        $this->info("🚀 Generating CRUD for {$name}...");
        $this->newLine();

        // Generate Livewire Component
        $componentPath = $this->generateComponent($name, $model, $fields, $searchable, $sortable);

        // Generate View
        $viewPath = $this->generateView($name, $fields);

        $this->newLine();
        $this->info("✅ CRUD generated successfully!");
        $this->newLine();
        $this->line("📁 Component: {$componentPath}");
        $this->line("📁 View: {$viewPath}");
        $this->newLine();
        $this->comment("💡 Don't forget to register the component in your HyroServiceProvider!");
        $this->comment("   Livewire::component('hyro.{$this->getKebabName($name)}-manager', \\App\\Livewire\\Admin\\{$name}Manager::class);");

        return 0;
    }

    protected function generateComponent($name, $model, $fields, $searchable, $sortable)
    {
        $componentName = "{$name}Manager";
        $componentPath = app_path("Livewire/Admin/{$componentName}.php");

        // Check if file exists
        if (File::exists($componentPath) && !$this->option('force')) {
            $this->warn("Component already exists: {$componentPath}");
            if (!$this->confirm('Overwrite?')) {
                return $componentPath;
            }
        }

        // Create directory if not exists
        if (!File::isDirectory(dirname($componentPath))) {
            File::makeDirectory(dirname($componentPath), 0755, true);
        }

        $fieldProperties = $this->generateFieldProperties($fields);
        $searchableFields = empty($searchable) ? array_keys($fields) : $searchable;
        $tableColumns = empty($sortable) ? array_keys($fields) : $sortable;

        $stub = $this->getComponentStub($name, $model, $fieldProperties, $fields, $searchableFields, $tableColumns);

        File::put($componentPath, $stub);
        return $componentPath;
    }

    protected function generateView($name, $fields)
    {
        $viewName = $this->getKebabName($name) . "-manager";
        $viewPath = resource_path("views/livewire/admin/{$viewName}.blade.php");

        // Check if file exists
        if (File::exists($viewPath) && !$this->option('force')) {
            $this->warn("View already exists: {$viewPath}");
            if (!$this->confirm('Overwrite?')) {
                return $viewPath;
            }
        }

        // Create directory if not exists
        if (!File::isDirectory(dirname($viewPath))) {
            File::makeDirectory(dirname($viewPath), 0755, true);
        }

        $stub = $this->getViewStub($name);
        File::put($viewPath, $stub);

        return $viewPath;
    }

    protected function getComponentStub($name, $model, $fieldProperties, $fields, $searchable, $table)
    {
        $namespace = "App\\Livewire\\Admin";
        $componentName = "{$name}Manager";

        return <<<PHP
<?php

namespace {$namespace};

use Marufsharia\Hyro\Livewire\BaseCrudComponent;
use {$model};
use Illuminate\Support\Str;

class {$componentName} extends BaseCrudComponent
{
{$fieldProperties}

    protected function getModel(): string
    {
        return {$model}::class;
    }

    protected function getFields(): array
    {
        return [
{$this->generateFieldsConfig($fields)}
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['{$this->implodeQuoted($searchable)}'];
    }

    protected function getTableColumns(): array
    {
        return ['{$this->implodeQuoted($table)}'];
    }

    protected function canUpdate(\$record): bool
    {
        return auth()->user()->hasPrivilege('{$this->getKebabName($name)}.update');
    }

    protected function canDelete(\$record): bool
    {
        return auth()->user()->hasPrivilege('{$this->getKebabName($name)}.delete');
    }
}
PHP;
    }

    protected function getViewStub($name)
    {
        return <<<BLADE
<div>
    @php
        \$title = '{$name} Management';
        \$description = 'Create, edit, and manage {$name} records';
    @endphp

    @include('hyro::livewire.partials.crud-table')
</div>
BLADE;
    }

    protected function parseFields($fieldsString)
    {
        if (!$fieldsString) {
            return [];
        }

        $fields = [];
        $parts = explode(',', $fieldsString);

        foreach ($parts as $part) {
            $segments = explode(':', trim($part));
            $fieldName = $segments[0];
            $fieldType = $segments[1] ?? 'string';
            $rules = $segments[2] ?? 'required';

            $fields[$fieldName] = [
                'type' => $this->mapFieldType($fieldType),
                'rules' => $rules,
            ];
        }

        return $fields;
    }

    protected function parseList($listString)
    {
        if (!$listString) {
            return [];
        }

        return array_map('trim', explode(',', $listString));
    }

    protected function mapFieldType($type)
    {
        return match (strtolower($type)) {
            'string', 'varchar' => 'text',
            'text', 'longtext' => 'textarea',
            'int', 'integer', 'bigint' => 'number',
            'boolean', 'bool' => 'checkbox',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'email' => 'email',
            'password' => 'password',
            'file', 'image' => 'file',
            'select' => 'select',
            default => 'text',
        };
    }

    protected function generateFieldProperties($fields)
    {
        $properties = [];

        foreach (array_keys($fields) as $field) {
            $properties[] = "    public \${$field};";
        }

        return implode("\n", $properties);
    }

    protected function generateFieldsConfig($fields)
    {
        $config = [];

        foreach ($fields as $name => $field) {
            $label = Str::title(str_replace('_', ' ', $name));
            $type = $field['type'];
            $rules = $field['rules'];

            $config[] = <<<CONFIG
            '{$name}' => [
                'type' => '{$type}',
                'label' => '{$label}',
                'rules' => '{$rules}',
                'default' => '',
            ]
CONFIG;
        }

        return implode(",\n", $config);
    }

    protected function implodeQuoted($array)
    {
        return implode("', '", $array);
    }

    protected function getKebabName($name)
    {
        return Str::kebab($name);
    }
}
