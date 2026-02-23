<?php

namespace Marufsharia\Hyro\Crud\Services\Crud\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Crud\Services\Crud\CodeGeneratorService;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\CrudConfiguration;
use Marufsharia\Hyro\Crud\Services\Crud\StubManagerService;

/**
 * Migration Generator
 * 
 * Generates migration files for CRUD resources.
 */
class MigrationGenerator
{
    public function __construct(
        protected StubManagerService $stubManager,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Generate migration file
     */
    public function generate(CrudConfiguration $config): string
    {
        $tableName = $config->getTableName();
        $migrationName = $config->getMigrationName();

        // Get stub content
        $stub = $this->stubManager->getStub('migration');

        // Generate columns
        $columns = $this->codeGenerator->generateMigrationColumns(
            $config->fields,
            $config->softDeletes,
            $config->timestamps
        );

        // Prepare replacements
        $replacements = [
            'tableName' => $tableName,
            'columns' => $columns,
            'softDeletes' => $config->softDeletes ? '            $table->softDeletes();' : '',
            'timestamps' => $config->timestamps ? '            $table->timestamps();' : '',
        ];

        // Replace placeholders
        $content = $this->stubManager->replace($stub, $replacements);

        // Generate file path
        $timestamp = date('Y_m_d_His');
        $migrationPath = database_path("migrations/{$timestamp}_{$migrationName}.php");

        // Write file
        File::put($migrationPath, $content);

        return $migrationPath;
    }

    /**
     * Check if migration exists
     */
    public function exists(CrudConfiguration $config): bool
    {
        $migrationName = $config->getMigrationName();
        $migrations = File::files(database_path('migrations'));

        foreach ($migrations as $migration) {
            if (Str::contains($migration->getFilename(), $migrationName)) {
                return true;
            }
        }

        return false;
    }
}
