<?php

namespace Marufsharia\Hyro\Crud\Services\Crud\Generators;

use Illuminate\Support\Facades\File;
use Marufsharia\Hyro\Crud\Services\Crud\CodeGeneratorService;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\CrudConfiguration;
use Marufsharia\Hyro\Crud\Services\Crud\StubManagerService;

/**
 * Model Generator
 * 
 * Generates or updates model files for CRUD resources.
 */
class ModelGenerator
{
    public function __construct(
        protected StubManagerService $stubManager,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Generate or update model file
     */
    public function generate(CrudConfiguration $config, bool $force = false): string
    {
        $modelName = $config->getModelClass();
        $modelPath = app_path("Models/{$modelName}.php");

        // Check if model exists
        if (File::exists($modelPath) && !$force) {
            return $modelPath;
        }

        // Get stub content
        $stub = $this->stubManager->getStub('model');

        // Generate code snippets
        $fillable = $this->codeGenerator->generateFillable($config->fields);
        $casts = $this->codeGenerator->generateCasts($config->fields);
        $relations = $this->codeGenerator->generateRelations($config->relations);

        // Prepare replacements
        $replacements = [
            'namespace' => $config->getModelNamespace(),
            'modelName' => $modelName,
            'fillable' => $fillable,
            'casts' => $casts,
            'relations' => $relations,
            'softDeletesImport' => $config->softDeletes ? 'use Illuminate\\Database\\Eloquent\\SoftDeletes;' : '',
            'softDeletesTrait' => $config->softDeletes ? ', SoftDeletes' : '',
            'auditImport' => $config->audit ? 'use OwenIt\\Auditing\\Contracts\\Auditable;' : '',
            'auditTrait' => $config->audit ? ', \\OwenIt\\Auditing\\Auditable' : '',
        ];

        // Replace placeholders
        $content = $this->stubManager->replace($stub, $replacements);

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($modelPath));

        // Write file
        File::put($modelPath, $content);

        return $modelPath;
    }

    /**
     * Check if model exists
     */
    public function exists(CrudConfiguration $config): bool
    {
        return class_exists($config->model);
    }

    /**
     * Get model path
     */
    public function getPath(CrudConfiguration $config): string
    {
        $modelName = $config->getModelClass();
        return app_path("Models/{$modelName}.php");
    }
}
