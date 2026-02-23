<?php

namespace Marufsharia\Hyro\Crud\Services\Crud\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Crud\Services\Crud\CodeGeneratorService;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\CrudConfiguration;
use Marufsharia\Hyro\Crud\Services\Crud\StubManagerService;

/**
 * View Generator
 * 
 * Generates Blade view files for CRUD resources.
 */
class ViewGenerator
{
    public function __construct(
        protected StubManagerService $stubManager,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Generate view file
     */
    public function generate(CrudConfiguration $config, bool $force = false): string
    {
        $viewPath = $this->getPath($config);

        // Check if view exists
        if (File::exists($viewPath) && !$force) {
            return $viewPath;
        }

        // Get stub content with template support
        $stub = $this->stubManager->getStub(
            'view',
            $config->templateType,
            $config->templateName
        );

        // Generate code snippets
        $tableHeaders = $this->codeGenerator->generateTableHeaders(
            $config->getSortableFields(),
            $config->fields
        );
        
        $tableColumns = $this->codeGenerator->generateTableColumns(
            $config->getSortableFields(),
            $config->fields
        );

        $exportButton = $config->export 
            ? $this->codeGenerator->generateExportButton() 
            : '';

        // Prepare replacements
        $replacements = [
            'title' => Str::title($config->name) . ' Management',
            'description' => 'Create, edit, and manage ' . Str::plural($config->name),
            'permission' => Str::kebab($config->name),
            'resourceName' => $config->name,
            'resourceNamePlural' => Str::plural($config->name),
            'tableHeaders' => $tableHeaders,
            'tableColumns' => $tableColumns,
            'formFields' => $this->generateFormFields($config),
            'filterFields' => '<!-- Filter fields will be auto-generated -->',
            'exportButton' => $exportButton,
            'columnCount' => count($config->getSortableFields()) + 2,
        ];

        // Replace placeholders
        $content = $this->stubManager->replace($stub, $replacements);

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($viewPath));

        // Write file
        File::put($viewPath, $content);

        return $viewPath;
    }

    /**
     * Generate form fields HTML
     */
    protected function generateFormFields(CrudConfiguration $config): string
    {
        return $this->codeGenerator->generateFormFields($config->fields);
    }

    /**
     * Get view path
     */
    public function getPath(CrudConfiguration $config): string
    {
        $viewName = Str::kebab($config->name) . "-manager";
        $directory = $config->isFrontend() ? 'frontend' : 'admin';
        return resource_path("views/livewire/{$directory}/{$viewName}.blade.php");
    }

    /**
     * Check if view exists
     */
    public function exists(CrudConfiguration $config): bool
    {
        return File::exists($this->getPath($config));
    }
}
