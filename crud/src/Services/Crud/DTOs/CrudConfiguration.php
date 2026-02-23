<?php

namespace Marufsharia\Hyro\Services\Crud\DTOs;

use Illuminate\Support\Collection;

/**
 * CRUD Configuration Data Transfer Object
 * 
 * Holds all configuration for CRUD generation.
 */
class CrudConfiguration
{
    public function __construct(
        public readonly string $name,
        public readonly string $model,
        public readonly Collection $fields,
        public readonly array $searchable = [],
        public readonly array $sortable = [],
        public readonly array $filterable = [],
        public readonly array $relations = [],
        public readonly bool $softDeletes = false,
        public readonly bool $timestamps = true,
        public readonly bool $export = false,
        public readonly bool $import = false,
        public readonly bool $audit = false,
        public readonly bool $privileges = false,
        public readonly bool $migration = false,
        public readonly bool $route = true,
        public readonly bool $menu = false,
        public readonly bool $module = false,
        public readonly bool $frontend = false,
        public readonly bool $auth = true,
        public readonly string $template = 'admin.template1',
        public readonly string $templateType = 'admin',
        public readonly string $templateName = 'template1',
        public readonly bool $force = false,
    ) {}

    /**
     * Get model class name
     */
    public function getModelClass(): string
    {
        return class_basename($this->model);
    }

    /**
     * Get model namespace
     */
    public function getModelNamespace(): string
    {
        $parts = explode('\\', $this->model);
        array_pop();
        return implode('\\', $parts);
    }

    /**
     * Get table name
     */
    public function getTableName(): string
    {
        return str($this->name)->plural()->snake()->toString();
    }

    /**
     * Get singular name
     */
    public function getSingularName(): string
    {
        return str($this->name)->singular()->toString();
    }

    /**
     * Get plural name
     */
    public function getPluralName(): string
    {
        return str($this->name)->plural()->toString();
    }

    /**
     * Get component name
     */
    public function getComponentName(): string
    {
        return str($this->name)->studly()->toString() . 'Manager';
    }

    /**
     * Get component namespace
     */
    public function getComponentNamespace(): string
    {
        return $this->frontend 
            ? 'App\\Livewire\\Frontend'
            : 'App\\Livewire\\Admin';
    }

    /**
     * Get component class
     */
    public function getComponentClass(): string
    {
        return $this->getComponentNamespace() . '\\' . $this->getComponentName();
    }

    /**
     * Get view name
     */
    public function getViewName(): string
    {
        $prefix = $this->frontend ? 'frontend' : 'admin';
        return "livewire.{$prefix}." . str($this->name)->kebab()->toString() . '-manager';
    }

    /**
     * Get route name
     */
    public function getRouteName(): string
    {
        $prefix = $this->frontend ? 'frontend' : 'admin';
        return "{$prefix}." . str($this->name)->kebab()->plural()->toString();
    }

    /**
     * Get route path
     */
    public function getRoutePath(): string
    {
        $prefix = $this->frontend ? '' : 'admin/';
        return $prefix . str($this->name)->kebab()->plural()->toString();
    }

    /**
     * Get migration name
     */
    public function getMigrationName(): string
    {
        return 'create_' . $this->getTableName() . '_table';
    }

    /**
     * Get export service class
     */
    public function getExportServiceClass(): string
    {
        return 'App\\Services\\Export\\' . $this->getModelClass() . 'ExportService';
    }

    /**
     * Get import service class
     */
    public function getImportServiceClass(): string
    {
        return 'App\\Services\\Import\\' . $this->getModelClass() . 'ImportService';
    }

    /**
     * Get searchable fields
     */
    public function getSearchableFields(): array
    {
        if (!empty($this->searchable)) {
            return $this->searchable;
        }

        // Auto-detect searchable fields
        return $this->fields
            ->filter(fn(Field $field) => $field->searchable || $field->isTextField())
            ->pluck('name')
            ->toArray();
    }

    /**
     * Get sortable fields
     */
    public function getSortableFields(): array
    {
        if (!empty($this->sortable)) {
            return $this->sortable;
        }

        // All fields are sortable by default
        return $this->fields->pluck('name')->toArray();
    }

    /**
     * Get filterable fields
     */
    public function getFilterableFields(): array
    {
        if (!empty($this->filterable)) {
            return $this->filterable;
        }

        // Auto-detect filterable fields
        return $this->fields
            ->filter(fn(Field $field) => $field->filterable)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Check if using frontend template
     */
    public function isFrontend(): bool
    {
        return $this->frontend || $this->templateType === 'frontend';
    }

    /**
     * Check if using admin template
     */
    public function isAdmin(): bool
    {
        return !$this->isFrontend();
    }

    /**
     * Get required fields
     */
    public function getRequiredFields(): Collection
    {
        return $this->fields->filter(fn(Field $field) => $field->required);
    }

    /**
     * Get file upload fields
     */
    public function getFileUploadFields(): Collection
    {
        return $this->fields->filter(fn(Field $field) => $field->isFileUpload());
    }

    /**
     * Has file uploads
     */
    public function hasFileUploads(): bool
    {
        return $this->getFileUploadFields()->isNotEmpty();
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'model' => $this->model,
            'fields' => $this->fields->map(fn(Field $f) => $f->toArray())->toArray(),
            'searchable' => $this->searchable,
            'sortable' => $this->sortable,
            'filterable' => $this->filterable,
            'relations' => $this->relations,
            'soft_deletes' => $this->softDeletes,
            'timestamps' => $this->timestamps,
            'export' => $this->export,
            'import' => $this->import,
            'audit' => $this->audit,
            'privileges' => $this->privileges,
            'migration' => $this->migration,
            'route' => $this->route,
            'menu' => $this->menu,
            'module' => $this->module,
            'frontend' => $this->frontend,
            'auth' => $this->auth,
            'template' => $this->template,
            'template_type' => $this->templateType,
            'template_name' => $this->templateName,
            'force' => $this->force,
        ];
    }
}
