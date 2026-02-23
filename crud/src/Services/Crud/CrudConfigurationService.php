<?php

namespace Marufsharia\Hyro\Crud\Services\Crud;

use Illuminate\Support\Collection;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\CrudConfiguration;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\Field;

/**
 * CRUD Configuration Service
 * 
 * Parses and validates CRUD configuration from command options.
 */
class CrudConfigurationService
{
    public function __construct(
        protected FieldParserService $fieldParser
    ) {}

    /**
     * Create configuration from command options
     */
    public function fromCommandOptions(string $name, array $options): CrudConfiguration
    {
        // Parse fields
        $fields = $this->fieldParser->parseFieldsString($options['fields'] ?? '');

        // Parse template
        $template = $options['template'] ?? 'admin.template1';
        $templateParts = explode('.', $template);
        $templateType = $templateParts[0] ?? 'admin';
        $templateName = $templateParts[1] ?? 'template1';

        // Auto-set template_type to frontend if --frontend=true and template not explicitly set
        $frontend = $options['frontend'] ?? false;
        if ($frontend && !isset($options['template'])) {
            $templateType = 'frontend';
            $template = 'frontend.template1';
        }

        return new CrudConfiguration(
            name: $name,
            model: $options['model'] ?? "App\\Models\\{$name}",
            fields: $fields,
            searchable: $this->parseList($options['searchable'] ?? null) ?: $this->autoDetectSearchable($fields),
            sortable: $this->parseList($options['sortable'] ?? null) ?: $fields->pluck('name')->toArray(),
            filterable: $this->parseList($options['filterable'] ?? null),
            relations: $this->parseList($options['relations'] ?? null),
            softDeletes: $options['soft-deletes'] ?? false,
            timestamps: $options['timestamps'] ?? true,
            export: $options['export'] ?? false,
            import: $options['import'] ?? false,
            audit: $options['audit'] ?? false,
            privileges: $options['privileges'] ?? false,
            migration: $options['migration'] ?? false,
            route: $options['route'] ?? true,
            menu: $options['menu'] ?? false,
            module: $options['module'] ?? false,
            frontend: $frontend,
            auth: $options['auth'] ?? true,
            template: $template,
            templateType: $templateType,
            templateName: $templateName,
            force: $options['force'] ?? false,
        );
    }

    /**
     * Parse comma-separated list
     */
    protected function parseList(?string $listString): array
    {
        if (!$listString) {
            return [];
        }
        return array_map('trim', explode(',', $listString));
    }

    /**
     * Auto-detect searchable fields
     */
    protected function autoDetectSearchable(Collection $fields): array
    {
        return $fields
            ->filter(fn(Field $field) => $field->searchable || $field->isTextField())
            ->pluck('name')
            ->toArray();
    }

    /**
     * Validate configuration
     */
    public function validate(CrudConfiguration $config): array
    {
        $errors = [];

        if ($config->fields->isEmpty()) {
            $errors[] = 'No fields specified';
        }

        // Validate field names
        foreach ($config->fields as $field) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field->name)) {
                $errors[] = "Invalid field name: {$field->name}";
            }
        }

        // Validate searchable fields exist
        foreach ($config->searchable as $fieldName) {
            if (!$config->fields->contains('name', $fieldName)) {
                $errors[] = "Searchable field '{$fieldName}' does not exist";
            }
        }

        // Validate sortable fields exist
        foreach ($config->sortable as $fieldName) {
            if (!$config->fields->contains('name', $fieldName)) {
                $errors[] = "Sortable field '{$fieldName}' does not exist";
            }
        }

        return $errors;
    }

    /**
     * Check if model needs migration
     */
    public function shouldGenerateMigration(CrudConfiguration $config): bool
    {
        // Auto-enable migration if model doesn't exist and fields are provided
        if (!class_exists($config->model) && $config->fields->isNotEmpty()) {
            return true;
        }

        return $config->migration;
    }
}
