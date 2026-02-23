<?php

namespace Marufsharia\Hyro\Services\Crud;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Stub Manager Service
 * 
 * Manages stub files and replacements with caching.
 */
class StubManagerService
{
    protected array $cache = [];
    protected string $packageStubPath;
    protected string $publishedStubPath;

    public function __construct()
    {
        $this->packageStubPath = $this->getPackagePath() . '/stubs';
        $this->publishedStubPath = resource_path('stubs/hyro');
    }

    /**
     * Get stub content
     */
    public function getStub(string $stubName, ?string $templateType = null, ?string $templateName = null): string
    {
        $cacheKey = $this->getCacheKey($stubName, $templateType, $templateName);

        // Return from cache if available
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        // Load stub content
        $content = $this->loadStub($stubName, $templateType, $templateName);

        // Cache and return
        $this->cache[$cacheKey] = $content;
        return $content;
    }

    /**
     * Load stub from file system
     */
    protected function loadStub(string $stubName, ?string $templateType = null, ?string $templateName = null): string
    {
        // For component and view stubs, use template system
        if (in_array($stubName, ['component', 'view']) && $templateType && $templateName) {
            return $this->loadTemplateStub($stubName, $templateType, $templateName);
        }

        // 1️⃣ Published stub location (user customized)
        $publishedPath = "{$this->publishedStubPath}/crud/{$stubName}.stub";

        // 2️⃣ Package default stub location (fallback)
        $packagePath = "{$this->packageStubPath}/crud/{$stubName}.stub";

        // ✅ Use published stub if exists
        if (File::exists($publishedPath)) {
            Log::info("Using published stub: {$publishedPath}");
            return File::get($publishedPath);
        }

        // ✅ Otherwise fallback to package stub
        if (File::exists($packagePath)) {
            Log::info("Using package stub: {$packagePath}");
            return File::get($packagePath);
        }

        // ❌ If none found
        throw new \Exception("Stub file not found: {$stubName}.stub");
    }

    /**
     * Load template-specific stub
     */
    protected function loadTemplateStub(string $stubName, string $templateType, string $templateName): string
    {
        // 1️⃣ Published template stub (user customized)
        $publishedTemplatePath = "{$this->publishedStubPath}/templates/{$templateType}/{$templateName}/{$stubName}.stub";

        // 2️⃣ Package template stub
        $packageTemplatePath = "{$this->packageStubPath}/templates/{$templateType}/{$templateName}/{$stubName}.stub";

        // 3️⃣ Fallback to default stub (backward compatibility)
        $defaultStubPath = "{$this->packageStubPath}/crud/{$stubName}.stub";

        // ✅ Use published template if exists
        if (File::exists($publishedTemplatePath)) {
            Log::info("Using published template stub: {$publishedTemplatePath}");
            return File::get($publishedTemplatePath);
        }

        // ✅ Use package template if exists
        if (File::exists($packageTemplatePath)) {
            Log::info("Using package template stub: {$packageTemplatePath}");
            return File::get($packageTemplatePath);
        }

        // ✅ Fallback to default stub
        if (File::exists($defaultStubPath)) {
            Log::info("Using default stub (template not found): {$defaultStubPath}");
            return File::get($defaultStubPath);
        }

        // ❌ If none found
        throw new \Exception("Stub file not found: {$stubName}.stub (template: {$templateType}.{$templateName})");
    }

    /**
     * Replace placeholders in stub
     */
    public function replace(string $stub, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $stub = str_replace("{{ {$search} }}", $replace, $stub);
        }

        return $stub;
    }

    /**
     * Get cache key
     */
    protected function getCacheKey(string $stubName, ?string $templateType, ?string $templateName): string
    {
        if ($templateType && $templateName) {
            return "{$stubName}:{$templateType}:{$templateName}";
        }
        return $stubName;
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Get package path (supports both local development and vendor installation)
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

        // Fallback: try to detect from the service class location
        $reflection = new \ReflectionClass($this);
        $servicePath = dirname($reflection->getFileName());
        // Navigate up from src/Services/Crud to package root
        return dirname(dirname(dirname($servicePath)));
    }

    /**
     * Check if stub exists
     */
    public function stubExists(string $stubName, ?string $templateType = null, ?string $templateName = null): bool
    {
        try {
            $this->getStub($stubName, $templateType, $templateName);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all available stubs
     */
    public function getAvailableStubs(): array
    {
        $stubs = [];

        // Get package stubs
        $packageCrudPath = "{$this->packageStubPath}/crud";
        if (File::exists($packageCrudPath)) {
            $files = File::files($packageCrudPath);
            foreach ($files as $file) {
                $stubs[] = $file->getFilenameWithoutExtension();
            }
        }

        // Get published stubs
        $publishedCrudPath = "{$this->publishedStubPath}/crud";
        if (File::exists($publishedCrudPath)) {
            $files = File::files($publishedCrudPath);
            foreach ($files as $file) {
                $name = $file->getFilenameWithoutExtension();
                if (!in_array($name, $stubs)) {
                    $stubs[] = $name;
                }
            }
        }

        return $stubs;
    }

    /**
     * Get available templates
     */
    public function getAvailableTemplates(): array
    {
        $templates = [];

        // Get package templates
        $packageTemplatesPath = "{$this->packageStubPath}/templates";
        if (File::exists($packageTemplatesPath)) {
            $types = File::directories($packageTemplatesPath);
            foreach ($types as $typePath) {
                $type = basename($typePath);
                $templateDirs = File::directories($typePath);
                foreach ($templateDirs as $templatePath) {
                    $template = basename($templatePath);
                    $templates[] = "{$type}.{$template}";
                }
            }
        }

        return $templates;
    }
}
