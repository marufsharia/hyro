<?php

namespace Marufsharia\Hyro\Crud\Services\Crud;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\CrudConfiguration;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\Field;

/**
 * Code Generator Service
 * 
 * Generates code snippets for CRUD components.
 */
class CodeGeneratorService
{
    /**
     * Generate migration columns
     */
    public function generateMigrationColumns(Collection $fields, bool $softDeletes = false, bool $timestamps = true): string
    {
        $columns = [];
        
        foreach ($fields as $field) {
            $column = $this->generateMigrationColumn($field);
            if ($column) {
                $columns[] = "            {$column}";
            }
        }

        return implode("\n", $columns);
    }

    /**
     * Generate single migration column
     */
    protected function generateMigrationColumn(Field $field): string
    {
        $type = $field->getDatabaseType();
        $nullable = $field->nullable ? '->nullable()' : '';
        $unique = isset($field->attributes['unique']) && $field->attributes['unique'] ? '->unique()' : '';
        $default = $this->getMigrationDefault($field);

        return match ($type) {
            'string' => "\$table->string('{$field->name}'){$nullable}{$unique}{$default};",
            'text' => "\$table->text('{$field->name}'){$nullable}{$default};",
            'integer' => "\$table->integer('{$field->name}'){$nullable}{$default};",
            'decimal' => "\$table->decimal('{$field->name}', 10, 2){$nullable}{$default};",
            'boolean' => "\$table->boolean('{$field->name}')->default(false){$nullable};",
            'date' => "\$table->date('{$field->name}'){$nullable}{$default};",
            'timestamp' => "\$table->timestamp('{$field->name}'){$nullable}{$default};",
            'time' => "\$table->time('{$field->name}'){$nullable}{$default};",
            'json' => "\$table->json('{$field->name}'){$nullable}{$default};",
            default => "\$table->string('{$field->name}'){$nullable}{$unique}{$default};",
        };
    }

    /**
     * Get migration default value
     */
    protected function getMigrationDefault(Field $field): string
    {
        if ($field->default === null) {
            return '';
        }

        if (is_bool($field->default)) {
            return '->default(' . ($field->default ? 'true' : 'false') . ')';
        }

        if (is_numeric($field->default)) {
            return "->default({$field->default})";
        }

        return "->default('{$field->default}')";
    }

    /**
     * Generate model fillable array
     */
    public function generateFillable(Collection $fields): string
    {
        $fillable = $fields->pluck('name')->toArray();
        return $this->formatArrayForPhp($fillable, true, '        ');
    }

    /**
     * Generate model casts array
     */
    public function generateCasts(Collection $fields): string
    {
        $casts = [];
        
        foreach ($fields as $field) {
            $cast = $field->getCastType();
            if ($cast) {
                $casts[] = "'{$field->name}' => '{$cast}'";
            }
        }

        return implode(",\n        ", $casts);
    }

    /**
     * Generate component properties
     */
    public function generateProperties(Collection $fields): string
    {
        $properties = [];
        
        foreach ($fields as $field) {
            $properties[] = "    public \${$field->name};";
        }

        return implode("\n", $properties);
    }

    /**
     * Generate fields configuration for component
     */
    public function generateFieldsConfig(Collection $fields): string
    {
        $config = [];
        
        foreach ($fields as $field) {
            $config[] = "            '{$field->name}' => [
                'type' => '{$field->type}',
                'label' => '{$field->label}',
                'rules' => '{$field->getValidationRules()}',
                'default' => " . $this->formatDefaultValue($field->default) . ",
            ]";
        }

        return implode(",\n", $config);
    }

    /**
     * Generate table headers
     */
    public function generateTableHeaders(array $sortableFields, Collection $fields): string
    {
        $headers = [];
        
        foreach ($sortableFields as $fieldName) {
            $field = $fields->firstWhere('name', $fieldName);
            $label = $field ? $field->label : Str::title(str_replace('_', ' ', $fieldName));
            
            $headers[] = "                            <th scope=\"col\"
                                wire:click=\"sortBy('{$fieldName}')\"
                                class=\"px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200\">
                                {$label}
                                @if(\$sortField === '{$fieldName}')
                                    <span class=\"ml-1\">{{ \$sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>";
        }

        return implode("\n", $headers);
    }

    /**
     * Generate table columns
     */
    public function generateTableColumns(array $sortableFields, Collection $fields): string
    {
        $columns = [];
        
        foreach ($sortableFields as $fieldName) {
            $field = $fields->firstWhere('name', $fieldName);
            
            if ($field) {
                $columns[] = $this->generateTableColumn($field);
            } else {
                $columns[] = "                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100\">
                                {{ \$item->{$fieldName} }}
                            </td>";
            }
        }

        return implode("\n", $columns);
    }

    /**
     * Generate single table column
     */
    protected function generateTableColumn(Field $field): string
    {
        if ($field->type === 'image') {
            return "                            <td class=\"px-6 py-4 whitespace-nowrap\">
                                @if(\$item->{$field->name})
                                    <img src=\"{{ Storage::disk('public')->url(\$item->{$field->name}) }}\"
                                         class=\"h-10 w-10 rounded-lg object-cover\">
                                @endif
                            </td>";
        }

        if ($field->type === 'checkbox') {
            return "                            <td class=\"px-6 py-4 whitespace-nowrap\">
                                <span class=\"px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ \$item->{$field->name} ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}\">
                                    {{ \$item->{$field->name} ? 'Yes' : 'No' }}
                                </span>
                            </td>";
        }

        if ($field->isDateField()) {
            return "                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100\">
                                {{ \$item->{$field->name}?->format('Y-m-d') }}
                            </td>";
        }

        return "                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100\">
                                {{ \$item->{$field->name} }}
                            </td>";
    }

    /**
     * Generate export headings
     */
    public function generateExportHeadings(array $sortableFields, Collection $fields): string
    {
        $headings = [];
        
        foreach ($sortableFields as $fieldName) {
            $field = $fields->firstWhere('name', $fieldName);
            $label = $field ? $field->label : Str::title(str_replace('_', ' ', $fieldName));
            $headings[] = "            '{$label}'";
        }

        return implode(",\n", $headings);
    }

    /**
     * Generate export mappings
     */
    public function generateExportMappings(array $sortableFields): string
    {
        $mappings = [];
        
        foreach ($sortableFields as $fieldName) {
            $mappings[] = "            \$item->{$fieldName}";
        }

        return implode(",\n", $mappings);
    }

    /**
     * Generate import attributes
     */
    public function generateImportAttributes(Collection $fields): string
    {
        $attributes = [];
        
        foreach ($fields as $field) {
            $attributes[] = "            '{$field->name}' => \$row['{$field->name}']";
        }

        return implode(",\n", $attributes);
    }

    /**
     * Generate import validation rules
     */
    public function generateImportRules(Collection $fields): string
    {
        $rules = [];
        
        foreach ($fields as $field) {
            $rules[] = "            '{$field->name}' => '{$field->getValidationRules()}'";
        }

        return implode(",\n", $rules);
    }

    /**
     * Generate export methods for component
     */
    public function generateExportMethods(string $resourceName): string
    {
        return "
    public function exportCsv()
    {
        return \\App\\Services\\Export\\{$resourceName}ExportService::toCsv(\$this->getItems()->getQuery());
    }

    public function exportExcel()
    {
        return \\App\\Services\\Export\\{$resourceName}ExportService::toExcel(\$this->getItems()->getQuery());
    }

    public function exportPdf()
    {
        return \\App\\Services\\Export\\{$resourceName}ExportService::toPdf(\$this->getItems()->getQuery());
    }";
    }

    /**
     * Generate import methods for component
     */
    public function generateImportMethods(string $resourceName): string
    {
        return "
    public \$importFile;

    public function import()
    {
        \$this->validate([
            'importFile' => 'required|mimes:csv,xlsx,xls|max:10240',
        ]);

        \$result = \\App\\Services\\Import\\{$resourceName}ImportService::fromFile(
            \$this->importFile->getRealPath()
        );

        if (\$result['success']) {
            \$this->alert('success', \"Imported {\$result['summary']['success']} records!\");
        } else {
            \$this->alert('error', \$result['message']);
        }
    }";
    }

    /**
     * Generate layout method for component
     */
    public function generateLayoutMethod(bool $isFrontend): string
    {
        if ($isFrontend) {
            return "    /**
     * The layout to use for this component
     */
    public \$layout = 'layouts.frontend';
";
        }

        return '';
    }

    /**
     * Generate export button HTML
     */
    public function generateExportButton(): string
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

    /**
     * Format array for PHP code
     */
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

    /**
     * Format default value for PHP code
     */
    protected function formatDefaultValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return "'{$value}'";
    }

    /**
     * Generate model relations
     */
    public function generateRelations(array $relations): string
    {
        if (empty($relations)) {
            return '';
        }

        $methods = [];

        foreach ($relations as $relation) {
            if (!str_contains($relation, ':')) {
                continue;
            }

            [$type, $related] = explode(':', $relation, 2);

            $method = match(strtolower($type)) {
                'belongsto' => $this->generateBelongsTo($related),
                'hasone' => $this->generateHasOne($related),
                'hasmany' => $this->generateHasMany($related),
                'belongstomany' => $this->generateBelongsToMany($related),
                default => null
            };

            if ($method) {
                $methods[] = $method;
            }
        }

        return implode("\n\n", $methods);
    }

    /**
     * Generate belongsTo relation
     */
    protected function generateBelongsTo(string $related): string
    {
        $method = Str::camel($related);
        return "    public function {$method}(): BelongsTo
    {
        return \$this->belongsTo({$related}::class);
    }";
    }

    /**
     * Generate hasOne relation
     */
    protected function generateHasOne(string $related): string
    {
        $method = Str::camel($related);
        return "    public function {$method}(): HasOne
    {
        return \$this->hasOne({$related}::class);
    }";
    }

    /**
     * Generate hasMany relation
     */
    protected function generateHasMany(string $related): string
    {
        $method = Str::camel(Str::plural($related));
        return "    public function {$method}(): HasMany
    {
        return \$this->hasMany({$related}::class);
    }";
    }

    /**
     * Generate belongsToMany relation
     */
    protected function generateBelongsToMany(string $related): string
    {
        $method = Str::camel(Str::plural($related));
        return "    public function {$method}(): BelongsToMany
    {
        return \$this->belongsToMany({$related}::class);
    }";
    }

    /**
     * Generate form fields HTML
     */
    public function generateFormFields(Collection $fields): string
    {
        $fieldHtmls = [];
        
        foreach ($fields as $field) {
            $fieldHtml = match($field->type) {
                'text', 'email', 'url', 'tel', 'password' => $this->generateTextInput($field),
                'textarea' => $this->generateTextarea($field),
                'number', 'decimal' => $this->generateNumberInput($field),
                'checkbox', 'boolean' => $this->generateCheckbox($field),
                'date' => $this->generateDateInput($field),
                'datetime' => $this->generateDatetimeInput($field),
                'time' => $this->generateTimeInput($field),
                'select' => $this->generateSelect($field),
                'radio' => $this->generateRadio($field),
                'file' => $this->generateFileInput($field),
                'image' => $this->generateImageInput($field),
                default => $this->generateTextInput($field)
            };
            
            $fieldHtmls[] = $fieldHtml;
        }

        return implode("\n\n", $fieldHtmls);
    }

    /**
     * Generate text input field
     */
    protected function generateTextInput($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <input 
                        type="{$field->type}"
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        {$required}
                    >
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate textarea field
     */
    protected function generateTextarea($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <textarea 
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        {$required}
                    ></textarea>
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate number input field
     */
    protected function generateNumberInput($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        $step = $field->type === 'decimal' ? '0.01' : '1';
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <input 
                        type="number"
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        step="{$step}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        {$required}
                    >
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate checkbox field
     */
    protected function generateCheckbox($field): string
    {
        return <<<HTML
                <div class="mb-4">
                    <div class="flex items-center">
                        <input 
                            type="checkbox"
                            id="{$field->name}"
                            wire:model="{$field->name}"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <label for="{$field->name}" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            {$field->label}
                        </label>
                    </div>
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate date input field
     */
    protected function generateDateInput($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <input 
                        type="date"
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        {$required}
                    >
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate datetime input field
     */
    protected function generateDatetimeInput($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <input 
                        type="datetime-local"
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        {$required}
                    >
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate time input field
     */
    protected function generateTimeInput($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <input 
                        type="time"
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        {$required}
                    >
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate select dropdown field
     */
    protected function generateSelect($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        $options = !empty($field->options) ? $field->options : ['option1' => 'Option 1', 'option2' => 'Option 2'];
        
        $optionsHtml = '';
        foreach ($options as $value => $label) {
            $optionsHtml .= "\n                        <option value=\"{$value}\">{$label}</option>";
        }
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <select 
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        {$required}
                    >
                        <option value="">Select {$field->label}</option>{$optionsHtml}
                    </select>
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate radio buttons field
     */
    protected function generateRadio($field): string
    {
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        $options = !empty($field->options) ? $field->options : ['option1' => 'Option 1', 'option2' => 'Option 2'];
        
        $optionsHtml = '';
        foreach ($options as $value => $label) {
            $optionsHtml .= <<<HTML

                        <div class="flex items-center">
                            <input 
                                type="radio"
                                id="{$field->name}_{$value}"
                                wire:model="{$field->name}"
                                value="{$value}"
                                class="border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <label for="{$field->name}_{$value}" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                {$label}
                            </label>
                        </div>
            HTML;
        }
        
        return <<<HTML
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {$field->label} {$requiredMark}
                    </label>
                    <div class="space-y-2">{$optionsHtml}
                    </div>
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate file input field
     */
    protected function generateFileInput($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <input 
                        type="file"
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        {$required}
                    >
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }

    /**
     * Generate image input field with preview
     */
    protected function generateImageInput($field): string
    {
        $required = $field->required ? 'required' : '';
        $requiredMark = $field->required ? '<span class="text-red-500">*</span>' : '';
        
        return <<<HTML
                <div class="mb-4">
                    <label for="{$field->name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {$field->label} {$requiredMark}
                    </label>
                    <input 
                        type="file"
                        id="{$field->name}"
                        wire:model="{$field->name}"
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        {$required}
                    >
                    @if(\${$field->name})
                        <div class="mt-2">
                            <img src="{{ \${$field->name}->temporaryUrl() }}" class="h-32 w-32 object-cover rounded-md">
                        </div>
                    @endif
                    @error('{$field->name}')
                        <p class="mt-1 text-sm text-red-600">{{ \$message }}</p>
                    @enderror
                </div>
        HTML;
    }
}
