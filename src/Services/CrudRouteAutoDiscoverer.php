<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Professional Auto-Discovery Service for CRUD Routes
 *
 * Automatically discovers all CRUD components and registers routes
 */
class CrudRouteAutoDiscoverer
{
    protected array $discoveredComponents = [];
    protected array $errors = [];
    protected array $warnings = [];

    /**
     * Discover and register all CRUD routes
     */
    public function discoverAndRegister(): array
    {
        $this->discoveredComponents = [];
        $this->errors = [];
        $this->warnings = [];

        // Discover components
        $components = $this->discoverCrudComponents();

        // Build route mappings
        $routes = $this->buildRouteMapping($components);

        // Generate route file
        $this->generateRouteFile($routes);

        return [
            'discovered' => count($this->discoveredComponents),
            'registered' => count($routes),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Discover all CRUD components
     */
    protected function discoverCrudComponents(): array
    {
        $components = [];
        $searchPaths = $this->getSearchPaths();

        foreach ($searchPaths as $namespace => $path) {
            if (!File::exists($path)) {
                $this->warnings[] = "Path does not exist: {$path}";
                continue;
            }

            $foundComponents = $this->scanDirectory($path, $namespace);
            $components = array_merge($components, $foundComponents);
        }

        $this->discoveredComponents = $components;
        return $components;
    }

    /**
     * Get search paths for components
     */
    protected function getSearchPaths(): array
    {
        return [
            'App\\Livewire\\Admin' => app_path('Livewire/Admin'),
            'App\\Http\\Livewire\\Admin' => app_path('Http/Livewire/Admin'),
        ];
    }

    /**
     * Scan directory for Manager components
     */
    protected function scanDirectory(string $path, string $namespace): array
    {
        $components = [];

        try {
            $files = File::allFiles($path);

            foreach ($files as $file) {
                if (!str_ends_with($file->getFilename(), 'Manager.php')) {
                    continue;
                }

                $className = $namespace . '\\' . $file->getBasename('.php');

                // Verify class exists and extends BaseCrudComponent
                if ($this->isValidCrudComponent($className)) {
                    $components[] = $className;
                } else {
                    $this->warnings[] = "Skipped {$className} - not a valid CRUD component";
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = "Error scanning {$path}: " . $e->getMessage();
            Log::error("Route discovery error in {$path}: " . $e->getMessage());
        }

        return $components;
    }

    /**
     * Check if class is a valid CRUD component
     */
    protected function isValidCrudComponent(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        try {
            $reflection = new \ReflectionClass($className);

            // Must extend BaseCrudComponent
            if (!$reflection->isSubclassOf(\HyroPlugins\PhoneBook\Livewire\BaseCrudComponent::class)) {
                return false;
            }

            // Must not be abstract
            if ($reflection->isAbstract()) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->errors[] = "Error reflecting {$className}: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Build route mapping from components
     */
    protected function buildRouteMapping(array $components): array
    {
        $routes = [];

        foreach ($components as $componentClass) {
            $routeName = $this->getRouteNameFromComponent($componentClass);

            if (!$routeName) {
                $this->warnings[] = "Could not determine route name for {$componentClass}";
                continue;
            }

            // Avoid duplicates
            if (isset($routes[$routeName])) {
                $this->warnings[] = "Duplicate route name '{$routeName}' for {$componentClass}";
                continue;
            }

            $routes[$routeName] = [
                'class' => $componentClass,
                'path' => $routeName,
                'name' => Str::kebab(Str::singular($routeName)),
            ];
        }

        return $routes;
    }

    /**
     * Extract route name from component class
     */
    protected function getRouteNameFromComponent(string $componentClass): ?string
    {
        try {
            $className = class_basename($componentClass);

            // Remove 'Manager' suffix
            if (str_ends_with($className, 'Manager')) {
                $resourceName = substr($className, 0, -7);
            } else {
                $resourceName = $className;
            }

            // Convert to kebab-case plural
            return Str::kebab(Str::plural($resourceName));
        } catch (\Exception $e) {
            $this->errors[] = "Error getting route name for {$componentClass}: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Generate the route file
     */
    protected function generateRouteFile(array $routes): void
    {
        $routeFile = base_path('routes/hyro-admin.php');

        try {
            $content = $this->buildRouteFileContent($routes);

            // Backup existing file if it exists
            // if (File::exists($routeFile)) {
            //     $backupFile = $routeFile . '.backup.' . time();
            //     File::copy($routeFile, $backupFile);
            // }

            File::put($routeFile, $content);

            Log::info("Generated CRUD routes file with " . count($routes) . " routes");
        } catch (\Exception $e) {
            $this->errors[] = "Error generating route file: " . $e->getMessage();
            Log::error("Route file generation error: " . $e->getMessage());
        }
    }

    /**
     * Build route file content
     */
    protected function buildRouteFileContent(array $routes): string
    {
        $content = $this->getRouteFileHeader();

        // Add dashboard
        $content .= $this->getBuiltInRoutes();

        // Add auto-discovered routes
        if (!empty($routes)) {
            $content .= "\n        // Auto-discovered CRUD Routes\n";

            foreach ($routes as $routeName => $routeInfo) {
                // Skip built-in routes
                if (in_array($routeName, ['users', 'roles', 'privileges'])) {
                    continue;
                }

                $content .= $this->formatRoute($routeInfo);
            }
        }

        $content .= $this->getRouteFileFooter();

        return $content;
    }

    /**
     * Get route file header
     */
    protected function getRouteFileHeader(): string
    {
        return <<<'PHP'
<?php

/**
 * Hyro Admin Routes - Auto-generated
 *
 * DO NOT EDIT MANUALLY - This file is automatically generated
 * Run: php artisan hyro:discover-routes to regenerate
 *
 * Generated: {timestamp}
 */

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Middleware\Authenticate;

Route::middleware(['web', Authenticate::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

PHP;
    }

    /**
     * Get built-in routes
     */
    protected function getBuiltInRoutes(): string
    {
        return <<<'PHP'
        // Dashboard
        Route::get('/dashboard', function () {
            return view('hyro::admin.dashboard');
        })->name('dashboard');

        // Core Management Routes
        Route::get('/users', \HyroPlugins\PhoneBook\Livewire\Admin\UserManager::class)
            ->name('users');

        Route::get('/roles', \HyroPlugins\PhoneBook\Livewire\Admin\RoleManager::class)
            ->name('roles');

        Route::get('/privileges', \HyroPlugins\PhoneBook\Livewire\Admin\PrivilegeManager::class)
            ->name('privileges');

PHP;
    }

    /**
     * Format individual route
     */
    protected function formatRoute(array $routeInfo): string
    {
        return <<<PHP
        Route::get('/{$routeInfo['path']}', \\{$routeInfo['class']}::class)
            ->name('{$routeInfo['name']}');

PHP;
    }

    /**
     * Get route file footer
     */
    protected function getRouteFileFooter(): string
    {
        return <<<'PHP'
    });

PHP;
    }

    /**
     * Get statistics about discovered routes
     */
    public function getStatistics(): array
    {
        return [
            'total_discovered' => count($this->discoveredComponents),
            'total_errors' => count($this->errors),
            'total_warnings' => count($this->warnings),
            'components' => $this->discoveredComponents,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }
}
