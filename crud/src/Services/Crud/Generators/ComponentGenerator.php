<?php

namespace Marufsharia\Hyro\Crud\Services\Crud\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Crud\Services\Crud\CodeGeneratorService;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\CrudConfiguration;
use Marufsharia\Hyro\Crud\Services\Crud\StubManagerService;

/**
 * Component Generator
 * 
 * Generates Livewire component files for CRUD resources.
 */
class ComponentGenerator
{
    public function __construct(
        protected StubManagerService $stubManager,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Generate component file
     */
    public function generate(CrudConfiguration $config, bool $force = false): string
    {
        $componentName = $config->getComponentName();
        $componentPath = $this->getPath($config);

        // Check if component exists
        if (File::exists($componentPath) && !$force) {
            return $componentPath;
        }

        // Get stub content with template support
        $stub = $this->stubManager->getStub(
            'component',
            $config->templateType,
            $config->templateName
        );

        // Generate code snippets
        $properties = $this->codeGenerator->generateProperties($config->fields);
        $fieldsConfig = $this->codeGenerator->generateFieldsConfig($config->fields);
        $layoutMethod = $this->codeGenerator->generateLayoutMethod($config->isFrontend());
        
        // Generate optional methods
        $exportMethods = $config->export 
            ? $this->codeGenerator->generateExportMethods($config->name) 
            : '';
        
        $importMethods = $config->import 
            ? $this->codeGenerator->generateImportMethods($config->name) 
            : '';

        // Prepare replacements
        $replacements = [
            'namespace' => $config->getComponentNamespace(),
            'componentName' => $componentName,
            'modelClass' => $config->model,
            'properties' => $properties,
            'layoutMethod' => $layoutMethod,
            'fields' => $fieldsConfig,
            'searchableFields' => $this->formatArray($config->getSearchableFields()),
            'tableColumns' => $this->formatArray($config->getSortableFields()),
            'permission' => Str::kebab($config->name),
            'relations' => $this->generateRelationsMethod($config),
            'filters' => '', // TODO: Implement filters
            'exportMethods' => $exportMethods,
            'importMethods' => $importMethods,
            'customMethods' => '',
        ];

        // Replace placeholders
        $content = $this->stubManager->replace($stub, $replacements);

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($componentPath));

        // Write file
        File::put($componentPath, $content);

        return $componentPath;
    }

    /**
     * Generate relationships method
     */
    protected function generateRelationsMethod(CrudConfiguration $config): string
    {
        if (empty($config->relations)) {
            return '';
        }

        return "
    protected function withRelationships(): array
    {
        return [" . $this->formatArray($config->relations) . "];
    }";
    }

    /**
     * Format array for PHP code
     */
    protected function formatArray(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $formatted = array_map(fn($item) => "'{$item}'", $items);
        return implode(', ', $formatted);
    }

    /**
     * Get component path
     */
    public function getPath(CrudConfiguration $config): string
    {
        $componentName = $config->getComponentName();
        $directory = $config->isFrontend() ? 'Frontend' : 'Admin';
        return app_path("Livewire/{$directory}/{$componentName}.php");
    }

    /**
     * Check if component exists
     */
    public function exists(CrudConfiguration $config): bool
    {
        return File::exists($this->getPath($config));
    }
}
