<?php

namespace Marufsharia\Hyro\Console\Commands\Crud;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Services\ModuleManager;

class MakeCrudCommand extends Command
{
    protected $signature = 'hyro:make-crud
                            {name : The name of the CRUD resource}
                            {--model= : The model class to use}
                            {--fields= : Comma-separated list of fields with type and rules}
                            {--searchable= : Comma-separated list of searchable fields}
                            {--sortable= : Comma-separated list of sortable fields}
                            {--filterable= : Comma-separated list of filterable fields}
                            {--relations= : Comma-separated list of relationships}
                            {--soft-deletes : Enable soft deletes}
                            {--timestamps : Enable timestamps (default: true)}
                            {--export : Enable export functionality}
                            {--import : Enable import functionality}
                            {--audit : Enable audit logging}
                            {--privileges : Auto-create privileges}
                            {--migration : Generate migration file}
                            {--route : Auto-register route}
                            {--menu : Add to sidebar menu}
                            {--module : Manage CRUD Module}
                            {--frontend=false : Generate frontend route (default: false)}
                            {--auth=true : Require authentication (default: true)}
                            {--template=admin.template1 : Template to use (admin.template1, admin.template2, frontend.template1, etc.)}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate production-ready CRUD with Livewire, Tailwind CSS, Alpine.js';

    protected $generatedFiles = [];
    protected $warnings = [];
    protected $name;
    protected $model;
    protected $fields = [];
    protected $config = [];
    protected ?string $generatedMigration = null;
    public function handle()
    {
        $this->displayBanner();

        $this->name = $this->argument('name');
        $this->model = $this->option('model') ?: "App\\Models\\{$this->name}";

        $this->parseConfiguration();

        if (!$this->validate()) {
            return 1;
        }

        $this->displaySummary();

        if (!$this->confirm('Proceed with generation?', true)) {
            $this->info('Cancelled.');
            return 0;
        }

        $this->generate();
        $this->displayResults();

        return 0;
    }

    protected function displayBanner()
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════════╗');
        $this->line('║           HYRO ADVANCED CRUD GENERATOR v2.0                      ║');
        $this->line('║    Production-Ready • Zero Configuration • Beautiful UI          ║');
        $this->line('╚══════════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    protected function parseConfiguration()
    {
        $this->fields = $this->parseFields($this->option('fields'));
        $this->config['searchable'] = $this->parseList($this->option('searchable')) ?: $this->autoDetectSearchable();
        $this->config['sortable'] = $this->parseList($this->option('sortable')) ?: array_keys($this->fields);
        $this->config['filterable'] = $this->parseList($this->option('filterable'));
        $this->config['relations'] = $this->parseList($this->option('relations'));
        $this->config['soft_deletes'] = $this->option('soft-deletes');
        $this->config['timestamps'] = $this->option('timestamps') ?? true;
        $this->config['export'] = $this->option('export');
        $this->config['import'] = $this->option('import');
        $this->config['audit'] = $this->option('audit');
        $this->config['privileges'] = $this->option('privileges');
        $this->config['migration'] = $this->option('migration');
        $this->config['route'] = $this->option('route');
        $this->config['menu'] = $this->option('menu');
        $this->config['module'] = $this->option('module');
        
        // New template system options
        $this->config['frontend'] = $this->option('frontend') ?? false;
        $this->config['auth'] = $this->option('auth') ?? true;
        $this->config['template'] = $this->option('template') ?: 'admin.template1';
        
        // Parse template (format: admin.template1 or frontend.template1)
        $templateParts = explode('.', $this->config['template']);
        $this->config['template_type'] = $templateParts[0] ?? 'admin'; // admin or frontend
        $this->config['template_name'] = $templateParts[1] ?? 'template1';
        
        // Auto-set template_type to frontend if --frontend=true and template not explicitly set
        if ($this->config['frontend'] && !$this->option('template')) {
            $this->config['template_type'] = 'frontend';
            $this->config['template'] = 'frontend.template1';
        }
    }

    protected function validate()
    {
        if (empty($this->fields)) {
            $this->error('❌ No fields specified!');
            $this->newLine();
            $this->displayFieldsHelp();
            return false;
        }

        // Auto-enable migration if model doesn't exist and fields are provided
        if (!class_exists($this->model) && !empty($this->fields)) {
            if (!$this->option('migration')) {
                $this->config['migration'] = true;
                $this->info("ℹ️  Model doesn't exist. Auto-enabling migration generation...");
            }
        }

        return true;
    }

    protected function displaySummary()
    {
        $this->info('📋 Generation Summary:');
        $this->newLine();

        $this->line("  Resource:     <fg=cyan>{$this->name}</>");
        $this->line("  Model:        <fg=cyan>{$this->model}</>");
        $this->line("  Fields:       <fg=yellow>" . count($this->fields) . " fields</>");

        $this->newLine();
        $this->line('  Files to generate:');
        $this->line('    ✓ Livewire Component');
        $this->line('    ✓ Blade View (Tailwind CSS 4)');

        if ($this->config['migration']) $this->line('    ✓ Migration File');
        if ($this->config['export']) $this->line('    ✓ Export Service');
        if ($this->config['import']) $this->line('    ✓ Import Service');
        if ($this->config['menu']) $this->line('    ✓ Side Menu Register');
        if ($this->config['module']) $this->line('    ✓ Module Register');

        $this->newLine();
    }

    protected function generate()
    {
        $this->info('🚀 Generating files...');
        $this->newLine();

        // Always check and publish frontend layouts if using frontend template
        if ($this->config['frontend'] || $this->config['template_type'] === 'frontend') {
            $this->components->task('Checking/publishing frontend layouts', fn() => $this->ensureFrontendLayouts());
        }

        if ($this->config['migration']) {
            $this->components->task('Creating migration', fn() => $this->generateMigration());
        }

        $this->components->task('Checking/creating model', fn() => $this->ensureModel());
        $this->components->task('Generating Livewire component', fn() => $this->generateComponent());
        $this->components->task('Generating Blade view', fn() => $this->generateView());

        if ($this->config['export']) {
            $this->components->task('Generating export service', fn() => $this->generateExportService());
        }

        if ($this->config['import']) {
            $this->components->task('Generating import service', fn() => $this->generateImportService());
        }

        // Always register CRUD route
        $this->components->task('Registering CRUD route', fn() => $this->registerCrudRoute());

        if ($this->config['menu']) {
            $this->components->task(
                "Adding sidebar menu entry",
                fn() => $this->registerSidebarMenu()
            );
        }
        if ($this->option('module')) {
            $this->components->task(
                "Registering module",
                fn() => $this->registerModule()
            );
        }

        if ($this->config['privileges']) {
            $this->components->task('Creating privileges', fn() => $this->createPrivileges());
        }

        $this->components->task('Running optimizations', fn() => $this->runOptimizations());
    }

    protected function generateMigration()
    {
        $tableName = Str::snake(Str::pluralStudly($this->name));
        $migrationName = "create_{$tableName}_table";

        $stub = $this->getStubContent('migration');

        $columns = $this->generateMigrationColumns();

        $stub = str_replace('{{ tableName }}', $tableName, $stub);
        $stub = str_replace('{{ columns }}', $columns, $stub);
        $stub = str_replace('{{ softDeletes }}', $this->config['soft_deletes'] ? '            $table->softDeletes();' : '', $stub);
        $stub = str_replace('{{ timestamps }}', $this->config['timestamps'] ? '            $table->timestamps();' : '', $stub);

        $timestamp = date('Y_m_d_His');
        $migrationPath = database_path("migrations/{$timestamp}_{$migrationName}.php");

        File::put($migrationPath, $stub);
        $this->generatedFiles[] = $migrationPath;
        $this->generatedMigration = $migrationPath;
        return true;
    }

    protected function ensureModel()
    {
        if (class_exists($this->model)) {
            return true;
        }
        return $this->generateModel();
    }

    protected function generateModel()
    {
        $modelName = class_basename($this->model);
        $modelPath = app_path("Models/{$modelName}.php");

        if (File::exists($modelPath) && !$this->option('force')) {
            return true;
        }

        $stub = $this->getStubContent('model');

        $stub = str_replace('{{ namespace }}', 'App\\Models', $stub);
        $stub = str_replace('{{ modelName }}', $modelName, $stub);
        $stub = str_replace('{{ fillable }}', $this->generateFillable(), $stub);
        $stub = str_replace('{{ casts }}', $this->generateCasts(), $stub);
        $stub = str_replace('{{ relations }}', $this->generateRelations(), $stub);
        $stub = str_replace('{{ softDeletesImport }}', $this->config['soft_deletes'] ? 'use Illuminate\\Database\\Eloquent\\SoftDeletes;' : '', $stub);
        $stub = str_replace('{{ softDeletesTrait }}', $this->config['soft_deletes'] ? ', SoftDeletes' : '', $stub);
        $stub = str_replace('{{ auditImport }}', $this->config['audit'] ? 'use OwenIt\\Auditing\\Contracts\\Auditable;' : '', $stub);
        $stub = str_replace('{{ auditTrait }}', $this->config['audit'] ? ', \\OwenIt\\Auditing\\Auditable' : '', $stub);

        File::ensureDirectoryExists(dirname($modelPath));
        File::put($modelPath, $stub);

        $this->generatedFiles[] = $modelPath;
        return true;
    }

    protected function generateComponent()
    {
        $componentName = "{$this->name}Manager";
        $componentPath = app_path("Livewire/Admin/{$componentName}.php");

        if (File::exists($componentPath) && !$this->option('force')) {
            if (!$this->confirm("Component exists. Overwrite?")) {
                return false;
            }
        }

        $stub = $this->getStubContent('component');

        $stub = str_replace('{{ namespace }}', 'App\\Livewire\\Admin', $stub);
        $stub = str_replace('{{ componentName }}', $componentName, $stub);
        $stub = str_replace('{{ modelClass }}', $this->model, $stub);
        $stub = str_replace('{{ properties }}', $this->generateProperties(), $stub);
        $stub = str_replace('{{ layoutMethod }}', $this->generateLayoutMethod(), $stub);
        $stub = str_replace('{{ fields }}', $this->generateFieldsConfig(), $stub);
        $stub = str_replace('{{ searchableFields }}', $this->formatArrayForPhp($this->config['searchable']), $stub);
        $stub = str_replace('{{ tableColumns }}', $this->formatArrayForPhp($this->config['sortable']), $stub);
        $stub = str_replace('{{ permission }}', Str::kebab($this->name), $stub);
        $stub = str_replace('{{ relations }}', $this->generateComponentRelations(), $stub);
        $stub = str_replace('{{ filters }}', $this->generateFilters(), $stub);
        $stub = str_replace('{{ exportMethods }}', $this->config['export'] ? $this->generateExportMethods() : '', $stub);
        $stub = str_replace('{{ importMethods }}', $this->config['import'] ? $this->generateImportMethods() : '', $stub);
        $stub = str_replace('{{ customMethods }}', '', $stub);

        File::ensureDirectoryExists(dirname($componentPath));
        File::put($componentPath, $stub);

        $this->generatedFiles[] = $componentPath;
        return true;
    }

    protected function generateView()
    {
        $viewName = Str::kebab($this->name) . "-manager";
        $viewPath = resource_path("views/livewire/admin/{$viewName}.blade.php");

        if (File::exists($viewPath) && !$this->option('force')) {
            if (!$this->confirm("View exists. Overwrite?")) {
                return false;
            }
        }

        $stub = $this->getStubContent('view');

        $stub = str_replace('{{ title }}', Str::title($this->name) . ' Management', $stub);
        $stub = str_replace('{{ description }}', 'Create, edit, and manage ' . Str::plural($this->name), $stub);
        $stub = str_replace('{{ permission }}', Str::kebab($this->name), $stub);
        $stub = str_replace('{{ resourceName }}', $this->name, $stub);
        $stub = str_replace('{{ resourceNamePlural }}', Str::plural($this->name), $stub);
        $stub = str_replace('{{ tableHeaders }}', $this->generateTableHeaders(), $stub);
        $stub = str_replace('{{ tableColumns }}', $this->generateTableColumns(), $stub);
        $stub = str_replace('{{ formFields }}', $this->generateFormFields(), $stub);
        $stub = str_replace('{{ filterFields }}', $this->generateFilterFields(), $stub);
        $stub = str_replace('{{ exportButton }}', $this->config['export'] ? $this->generateExportButton() : '', $stub);
        $stub = str_replace('{{ columnCount }}', count($this->config['sortable']) + 2, $stub);

        File::ensureDirectoryExists(dirname($viewPath));
        File::put($viewPath, $stub);

        $this->generatedFiles[] = $viewPath;
        return true;
    }

    protected function generateExportService()
    {
        $exportPath = app_path("Services/Export/{$this->name}ExportService.php");

        $stub = $this->getStubContent('export');

        $stub = str_replace('{{ namespace }}', 'App\\Services\\Export', $stub);
        $stub = str_replace('{{ exportName }}', "{$this->name}ExportService", $stub);
        $stub = str_replace('{{ modelClass }}', $this->model, $stub);
        $stub = str_replace('{{ resourceName }}', $this->name, $stub);
        $stub = str_replace('{{ resourceNameKebab }}', Str::kebab($this->name), $stub);
        $stub = str_replace('{{ headings }}', $this->generateExportHeadings(), $stub);
        $stub = str_replace('{{ mappings }}', $this->generateExportMappings(), $stub);

        File::ensureDirectoryExists(dirname($exportPath));
        File::put($exportPath, $stub);

        $this->generatedFiles[] = $exportPath;
        return true;
    }

    protected function generateImportService()
    {
        $importPath = app_path("Services/Import/{$this->name}ImportService.php");

        $stub = $this->getStubContent('import');

        $stub = str_replace('{{ namespace }}', 'App\\Services\\Import', $stub);
        $stub = str_replace('{{ importName }}', "{$this->name}ImportService", $stub);
        $stub = str_replace('{{ modelClass }}', $this->model, $stub);
        $stub = str_replace('{{ modelAttributes }}', $this->generateImportAttributes(), $stub);
        $stub = str_replace('{{ validationRules }}', $this->generateImportRules(), $stub);
        $stub = str_replace('{{ validationMessages }}', $this->generateImportMessages(), $stub);

        File::ensureDirectoryExists(dirname($importPath));
        File::put($importPath, $stub);

        $this->generatedFiles[] = $importPath;
        return true;
    }

    protected function createPrivileges()
    {
        // This would integrate with Hyro privilege system
        $this->warnings[] = "Privileges feature requires manual integration with Hyro privilege system";
        return true;
    }

    /**
     * Ensure frontend layouts exist, publish if missing
     */
    protected function ensureFrontendLayouts()
    {
        $frontendLayoutPath = resource_path('views/layouts/frontend.blade.php');
        
        // Check if frontend layout exists
        if (File::exists($frontendLayoutPath)) {
            $this->line("   ✓ Frontend layout already exists");
            return true;
        }

        // Layout doesn't exist, publish it
        $this->line("   📦 Frontend layout not found, publishing...");
        return $this->publishFrontendLayouts();
    }

    protected function publishFrontendLayouts()
    {
        // Determine package path (supports both local development and vendor installation)
        $packagePath = $this->getPackagePath();
        
        // Define source and destination paths
        $layoutsToCopy = [
            [
                'source' => $packagePath . '/resources/views/layouts/frontend.blade.php',
                'destination' => resource_path('views/layouts/frontend.blade.php'),
                'name' => 'frontend.blade.php'
            ],
            [
                'source' => $packagePath . '/resources/views/layouts/partials/frontend-nav.blade.php',
                'destination' => resource_path('views/layouts/partials/frontend-nav.blade.php'),
                'name' => 'frontend-nav.blade.php'
            ],
            [
                'source' => $packagePath . '/resources/views/layouts/partials/frontend-footer.blade.php',
                'destination' => resource_path('views/layouts/partials/frontend-footer.blade.php'),
                'name' => 'frontend-footer.blade.php'
            ],
        ];

        $published = [];
        $existing = [];
        $failed = [];
        $needsAssets = false;

        foreach ($layoutsToCopy as $layout) {
            // Check if destination already exists
            if (File::exists($layout['destination'])) {
                $existing[] = $layout['name'];
                continue;
            }

            // Check if source exists
            if (!File::exists($layout['source'])) {
                $failed[] = $layout['name'] . ' (source not found)';
                continue;
            }

            // Create directory if it doesn't exist
            File::ensureDirectoryExists(dirname($layout['destination']));

            // Copy the file
            if (File::copy($layout['source'], $layout['destination'])) {
                $published[] = $layout['name'];
                $needsAssets = true; // Mark that we need to publish assets
            } else {
                $failed[] = $layout['name'] . ' (copy failed)';
            }
        }

        // Display status
        if (!empty($published)) {
            $this->line("   ✓ Published: " . implode(', ', $published));
        }

        if (!empty($existing)) {
            $this->line("   ✓ Already exists: " . implode(', ', $existing));
        }

        if (!empty($failed)) {
            $this->warn("   ⚠ Failed: " . implode(', ', $failed));
        }

        // Publish assets if layouts were published
        if ($needsAssets || !empty($published)) {
            $this->publishFrontendAssets();
        }

        return true;
    }

    /**
     * Publish frontend assets (CSS/JS)
     */
    protected function publishFrontendAssets()
    {
        $this->newLine();
        $this->line("   📦 Publishing frontend assets...");

        try {
            // Publish Hyro assets
            Artisan::call('vendor:publish', [
                '--tag' => 'hyro-assets',
                '--force' => false,
            ]);

            $this->line("   ✓ Assets published successfully");
        } catch (\Exception $e) {
            $this->warn("   ⚠ Failed to publish assets: " . $e->getMessage());
        }
    }

    /**
     * Get the package path (supports both local development and vendor installation)
     */
    protected function getPackagePath(): string
    {
        // Check if package is in local development (packages/marufsharia/hyro)
        $localPath = base_path('packages/marufsharia/hyro');
        if (File::exists($localPath)) {
            return $localPath;
        }

        // Check if package is installed via Composer (vendor/marufsharia/hyro)
        $vendorPath = base_path('vendor/marufsharia/hyro');
        if (File::exists($vendorPath)) {
            return $vendorPath;
        }

        // Fallback: try to detect from the command class location
        $reflection = new \ReflectionClass($this);
        $commandPath = dirname($reflection->getFileName());
        // Navigate up from src/Console/Commands/Crud to package root
        $packagePath = dirname(dirname(dirname(dirname($commandPath))));
        
        return $packagePath;
    }

    protected function runOptimizations()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('hyro:discover-routes');
        return true;
    }

    protected function displayResults()
    {
        $this->newLine();
        $this->info('✅ CRUD Generation Complete!');
        $this->newLine();

        $this->line('📁 Generated Files:');
        foreach ($this->generatedFiles as $file) {
            $this->line("   ✓ " . str_replace(base_path(), '', $file));
        }

        if (!empty($this->warnings)) {
            $this->newLine();
            $this->line('⚠️  Warnings:');
            foreach ($this->warnings as $warning) {
                $this->warn("   • {$warning}");
            }
        }

        $this->newLine();
        $this->line('🚀 Next Steps:');
        if ($this->config['migration']) {
            $this->line('   1. Run: php artisan migrate');
        }
        $this->line('   2. Access your CRUD at: /admin/' . Str::kebab(Str::plural($this->name)));
        $this->newLine();
    }

    // Helper Methods for stub generation

    protected function getStubContent(string $stubName): string
    {
        // For component and view stubs, use template system
        if (in_array($stubName, ['component', 'view'])) {
            return $this->getTemplateStubContent($stubName);
        }

        // 1️⃣ Published stub location (user customized)
        $publishedStubPath = resource_path("stubs/hyro/crud/{$stubName}.stub");

        // 2️⃣ Package default stub location (fallback)
        $packageStubPath = __DIR__ . "/../../../stubs/crud/{$stubName}.stub";

        // ✅ Use published stub if exists
        if (File::exists($publishedStubPath)) {
            Log::info("Using published stub: " . $publishedStubPath);
            return File::get($publishedStubPath);
        }

        // ✅ Otherwise fallback to package stub
        if (File::exists($packageStubPath)) {
            Log::info("Using package stub: " . $packageStubPath);
            return File::get($packageStubPath);
        }

        // ❌ If none found
        throw new \Exception("Stub file not found: {$stubName}.stub");
    }

    /**
     * Get stub content from template directory
     */
    protected function getTemplateStubContent(string $stubName): string
    {
        $templateType = $this->config['template_type']; // admin or frontend
        $templateName = $this->config['template_name']; // template1, template2, etc.

        // 1️⃣ Published template stub (user customized)
        $publishedTemplatePath = resource_path("stubs/hyro/templates/{$templateType}/{$templateName}/{$stubName}.stub");

        // 2️⃣ Package template stub
        $packageTemplatePath = __DIR__ . "/../../../stubs/templates/{$templateType}/{$templateName}/{$stubName}.stub";

        // 3️⃣ Fallback to default stub (backward compatibility)
        $defaultStubPath = __DIR__ . "/../../../stubs/crud/{$stubName}.stub";

        // ✅ Use published template if exists
        if (File::exists($publishedTemplatePath)) {
            Log::info("Using published template stub: " . $publishedTemplatePath);
            return File::get($publishedTemplatePath);
        }

        // ✅ Use package template if exists
        if (File::exists($packageTemplatePath)) {
            Log::info("Using package template stub: " . $packageTemplatePath);
            return File::get($packageTemplatePath);
        }

        // ✅ Fallback to default stub
        if (File::exists($defaultStubPath)) {
            Log::info("Using default stub (template not found): " . $defaultStubPath);
            $this->warnings[] = "Template {$templateType}.{$templateName} not found, using default stub";
            return File::get($defaultStubPath);
        }

        // ❌ If none found
        throw new \Exception("Stub file not found: {$stubName}.stub (template: {$templateType}.{$templateName})");
    }


    protected function parseFields(?string $fieldsString): array
    {
        if (!$fieldsString) {
            return [];
        }

        $fields = [];
        
        // Split by comma, but handle type definitions like decimal:10,2
        // Use regex to properly split field definitions
        preg_match_all('/([a-zA-Z_][a-zA-Z0-9_]*):([^:,]+(?::\d+,\d+)?|[^:,]+)(?::([^,]+))?(?:,|$)/', $fieldsString, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $fieldName = $match[1];
            $fieldType = $match[2] ?? 'string';
            $rules = $match[3] ?? 'nullable';

            // Extract base type and parameters (e.g., "decimal:10,2" -> "decimal")
            $baseType = explode(':', $fieldType)[0];

            $fields[$fieldName] = [
                'type' => $this->mapFieldType($baseType),
                'db_type' => $fieldType, // Keep full type with parameters
                'rules' => $rules,
                'label' => Str::title(str_replace('_', ' ', $fieldName)),
            ];
        }

        return $fields;
    }

    protected function parseList(?string $listString): array
    {
        if (!$listString) {
            return [];
        }
        return array_map('trim', explode(',', $listString));
    }

    protected function autoDetectSearchable(): array
    {
        $searchable = [];
        foreach ($this->fields as $name => $field) {
            if (in_array($field['type'], ['text', 'textarea', 'email'])) {
                $searchable[] = $name;
            }
        }
        return $searchable;
    }

    protected function mapFieldType(string $type): string
    {
        return match (strtolower($type)) {
            'string', 'varchar' => 'text',
            'text', 'longtext' => 'textarea',
            'int', 'integer', 'bigint' => 'number',
            'decimal', 'float', 'double' => 'decimal',
            'boolean', 'bool' => 'checkbox',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'time' => 'time',
            'email' => 'email',
            'password' => 'password',
            'file' => 'file',
            'image' => 'image',
            'select' => 'select',
            'radio' => 'radio',
            default => 'text',
        };
    }

    protected function generateMigrationColumns(): string
    {
        $columns = [];
        foreach ($this->fields as $name => $field) {
            $column = $this->getMigrationColumn($name, $field);
            if ($column) {
                $columns[] = "            {$column}";
            }
        }
        return implode("\n", $columns);
    }

    protected function getMigrationColumn(string $name, array $field): string
    {
        $type = $field['db_type'];
        $nullable = str_contains($field['rules'], 'nullable') ? '->nullable()' : '';
        $unique = str_contains($field['rules'], 'unique') ? '->unique()' : '';

        return match (strtolower($type)) {
            'string', 'varchar' => "\$table->string('{$name}'){$nullable}{$unique};",
            'text' => "\$table->text('{$name}'){$nullable};",
            'longtext' => "\$table->longText('{$name}'){$nullable};",
            'integer', 'int' => "\$table->integer('{$name}'){$nullable};",
            'bigint' => "\$table->bigInteger('{$name}'){$nullable};",
            'decimal' => "\$table->decimal('{$name}', 10, 2){$nullable};",
            'float' => "\$table->float('{$name}'){$nullable};",
            'boolean', 'bool' => "\$table->boolean('{$name}')->default(false){$nullable};",
            'date' => "\$table->date('{$name}'){$nullable};",
            'datetime', 'timestamp' => "\$table->timestamp('{$name}'){$nullable};",
            'time' => "\$table->time('{$name}'){$nullable};",
            'file', 'image' => "\$table->string('{$name}'){$nullable};",
            default => "\$table->string('{$name}'){$nullable};",
        };
    }

    protected function generateFillable(): string
    {
        $fillable = array_keys($this->fields);
        return $this->formatArrayForPhp($fillable, true, '        ');
    }

    protected function generateCasts(): string
    {
        $casts = [];
        foreach ($this->fields as $name => $field) {
            $cast = $this->getCast($field);
            if ($cast) {
                $casts[] = "'{$name}' => '{$cast}'";
            }
        }
        return implode(",\n        ", $casts);
    }

    protected function getCast(array $field): ?string
    {
        return match ($field['type']) {
            'number' => 'integer',
            'decimal' => 'decimal:2',
            'checkbox' => 'boolean',
            'date' => 'date',
            'datetime' => 'datetime',
            default => null,
        };
    }

    protected function generateRelations(): string
    {
        // Placeholder for relation generation
        return '';
    }

    protected function generateProperties(): string
    {
        $properties = [];
        foreach ($this->fields as $name => $field) {
            $properties[] = "    public \${$name};";
        }
        return implode("\n", $properties);
    }

    protected function generateFieldsConfig(): string
    {
        $config = [];
        foreach ($this->fields as $name => $field) {
            $config[] = "            '{$name}' => [
                'type' => '{$field['type']}',
                'label' => '{$field['label']}',
                'rules' => '{$field['rules']}',
                'default' => '',
            ]";
        }
        return implode(",\n", $config);
    }

    protected function generateComponentRelations(): string
    {
        if (empty($this->config['relations'])) {
            return '';
        }

        return "
    protected function withRelationships(): array
    {
        return [" . $this->formatArrayForPhp($this->config['relations']) . "];
    }";
    }

    protected function generateFilters(): string
    {
        // Placeholder
        return '';
    }

    protected function generateExportMethods(): string
    {
        return "
    public function exportCsv()
    {
        return \\App\\Services\\Export\\{$this->name}ExportService::toCsv(\$this->getItems()->getQuery());
    }

    public function exportExcel()
    {
        return \\App\\Services\\Export\\{$this->name}ExportService::toExcel(\$this->getItems()->getQuery());
    }

    public function exportPdf()
    {
        return \\App\\Services\\Export\\{$this->name}ExportService::toPdf(\$this->getItems()->getQuery());
    }";
    }

    protected function generateImportMethods(): string
    {
        return "
    public \$importFile;

    public function import()
    {
        \$this->validate([
            'importFile' => 'required|mimes:csv,xlsx,xls|max:10240',
        ]);

        \$result = \\App\\Services\\Import\\{$this->name}ImportService::fromFile(
            \$this->importFile->getRealPath()
        );

        if (\$result['success']) {
            \$this->alert('success', \"Imported {\$result['summary']['success']} records!\");
        } else {
            \$this->alert('error', \$result['message']);
        }
    }";
    }

    protected function generateTableHeaders(): string
    {
        $headers = [];
        foreach ($this->config['sortable'] as $field) {
            $label = Str::title(str_replace('_', ' ', $field));
            $headers[] = "                            <th scope=\"col\"
                                wire:click=\"sortBy('{$field}')\"
                                class=\"px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200\">
                                {$label}
                                @if(\$sortField === '{$field}')
                                    <span class=\"ml-1\">{{ \$sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>";
        }
        return implode("\n", $headers);
    }

    protected function generateTableColumns(): string
    {
        $columns = [];
        foreach ($this->config['sortable'] as $field) {
            if (isset($this->fields[$field])) {
                $fieldType = $this->fields[$field]['type'];
                $columns[] = $this->generateTableColumn($field, $fieldType);
            } else {
                $columns[] = "                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100\">
                                {{ \$item->{$field} }}
                            </td>";
            }
        }
        return implode("\n", $columns);
    }

    protected function generateTableColumn(string $field, string $type): string
    {
        if ($type === 'image') {
            return "                            <td class=\"px-6 py-4 whitespace-nowrap\">
                                @if(\$item->{$field})
                                    <img src=\"{{ Storage::disk('public')->url(\$item->{$field}) }}\"
                                         class=\"h-10 w-10 rounded-lg object-cover\">
                                @endif
                            </td>";
        }

        if ($type === 'checkbox') {
            return "                            <td class=\"px-6 py-4 whitespace-nowrap\">
                                <span class=\"px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ \$item->{$field} ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}\">
                                    {{ \$item->{$field} ? 'Yes' : 'No' }}
                                </span>
                            </td>";
        }

        return "                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100\">
                                {{ \$item->{$field} }}
                            </td>";
    }

    protected function generateFormFields(): string
    {
        $formFieldsStub = $this->getStubContent('form-fields');
        $fields = [];

        foreach ($this->fields as $fieldName => $field) {
            $fieldHtml = $formFieldsStub;
            // This would be processed based on field type
            // For now, simplified version
            $fields[] = "{{-- {$field['label']} field would be generated here --}}";
        }

        return implode("\n", $fields);
    }

    protected function generateFilterFields(): string
    {
        return '<!-- Filter fields will be auto-generated -->';
    }

    protected function generateExportButton(): string
    {
        return '
                <button
                    wire:click="exportCsv"
                    class="inline-flex items-center gap-2 px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export</span>
                </button>';
    }

    protected function generateExportHeadings(): string
    {
        $headings = [];
        foreach ($this->config['sortable'] as $field) {
            $headings[] = "            '" . Str::title(str_replace('_', ' ', $field)) . "'";
        }
        return implode(",\n", $headings);
    }

    protected function generateExportMappings(): string
    {
        $mappings = [];
        foreach ($this->config['sortable'] as $field) {
            $mappings[] = "            \$item->{$field}";
        }
        return implode(",\n", $mappings);
    }

    protected function generateImportAttributes(): string
    {
        $attributes = [];
        foreach ($this->fields as $name => $field) {
            $attributes[] = "            '{$name}' => \$row['{$name}']";
        }
        return implode(",\n", $attributes);
    }

    protected function generateImportRules(): string
    {
        $rules = [];
        foreach ($this->fields as $name => $field) {
            $rules[] = "            '{$name}' => '{$field['rules']}'";
        }
        return implode(",\n", $rules);
    }

    protected function generateImportMessages(): string
    {
        return "            // Custom messages here";
    }

    /**
     * Generate layout property for component based on frontend flag
     * Uses the existing getLayout() method from HasCrud trait
     */
    protected function generateLayoutMethod(): string
    {
        // If frontend is enabled, add layout property to use frontend layout
        // The getLayout() method in HasCrud trait will use this property
        if ($this->config['frontend']) {
            return "    /**
     * The layout to use for this component
     */
    public \$layout = 'layouts.frontend';
";
        }

        // For admin or default, return empty (uses default admin layout from config)
        return '';
    }

    protected function formatArrayForPhp(array $items, bool $quotes = true, string $indent = ''): string
    {
        if (empty($items)) {
            return '';
        }

        $formatted = array_map(function ($item) use ($quotes) {
            return $quotes ? "'{$item}'" : $item;
        }, $items);

        return implode(', ', $formatted);
    }

    protected function registerSidebarMenu(): bool
    {
        $this->info("Sidebar auto-updated from ModuleManager.");
        return true;
    }

    /**
     * Register CRUD route using SmartCrudRouteManager.
     *
     * @return bool
     */
    protected function registerCrudRoute(): bool
    {
        $routeManager = app(\Marufsharia\Hyro\Services\SmartCrudRouteManager::class);

        // Create backup before modifying routes
        $backupPath = $routeManager->backup();
        if ($backupPath) {
            $this->line("   📦 Backup created: " . basename($backupPath));
        }

        $componentClass = "App\\Livewire\\Admin\\" . Str::studly($this->name) . "Manager";
        $permission = Str::kebab($this->name);
        
        // Determine if this is a frontend or admin route
        $isFrontend = $this->config['frontend'];
        $requiresAuth = $this->config['auth'];

        // Build middleware array
        $middleware = [];
        if ($requiresAuth) {
            $middleware[] = 'auth';
        }

        $success = $routeManager->addRoute(
            $this->name,
            $componentClass,
            [
                'permission' => $permission,
                'middleware' => $middleware,
                'frontend' => $isFrontend,
                'auth' => $requiresAuth,
            ]
        );

        if ($success) {
            $routeName = Str::kebab(Str::plural($this->name));
            $routeType = $isFrontend ? 'frontend' : 'admin';
            $this->info("✓ Route registered: {$routeType}.{$routeName}");
            
            // Clean old backups (keep last 10)
            $deleted = $routeManager->cleanOldBackups(10);
            if ($deleted > 0) {
                $this->line("   🗑️  Cleaned {$deleted} old backup(s)");
            }
        } else {
            $this->warn("Route already exists or could not be registered");
        }

        return true;
    }

    protected function registerModule(): bool
    {
        $slug = Str::kebab(Str::plural($this->name));

        ModuleManager::register($slug, [
            "title" => Str::title(Str::plural($this->name)),
            "icon" => "folder",
            "group" => "Modules",
            "order" => 100,
            "component" => "App\\Livewire\\Admin\\" . Str::studly($this->name) . "Manager",

            "enabled" => true,

            "paths" => [
                "manager" => "app/Livewire/Admin/" . Str::studly($this->name) . "Manager.php",
                "view" => "resources/views/livewire/admin/{$slug}-manager.blade.php",
                "migration" => $this->generatedMigration ?? null,
            ],
        ]);

        return true;
    }


    protected function displayFieldsHelp()
    {
        $this->info('Field Syntax: field_name:type:validation_rules');
        $this->newLine();
        $this->line('Example:');
        $this->line('  --fields="title:string:required,body:text:required,featured_image:image:nullable"');
        $this->newLine();
        $this->line('Supported types: string, text, integer, decimal, boolean, date, datetime, email, file, image, select');
    }


}
