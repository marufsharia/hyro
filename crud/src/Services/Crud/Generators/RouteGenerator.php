<?php

namespace Marufsharia\Hyro\Crud\Services\Crud\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\CrudConfiguration;

/**
 * Route Generator
 * 
 * Registers routes for CRUD resources.
 */
class RouteGenerator
{
    /**
     * Register route for CRUD resource
     */
    public function register(CrudConfiguration $config): bool
    {
        $routeFile = $this->getRouteFile($config);
        
        // Ensure route file exists
        if (!File::exists($routeFile)) {
            $this->createRouteFile($routeFile, $config->isFrontend());
        }

        // Check if route already exists
        if ($this->routeExists($routeFile, $config)) {
            return false;
        }

        // Generate route
        $route = $this->generateRoute($config);

        // Append to file
        File::append($routeFile, "\n" . $route);

        return true;
    }

    /**
     * Get route file path
     */
    protected function getRouteFile(CrudConfiguration $config): string
    {
        if ($config->isFrontend()) {
            return base_path('routes/web.php');
        }
        
        // Admin routes in separate file
        $routeFile = base_path('routes/hyro/crud.php');
        File::ensureDirectoryExists(dirname($routeFile));
        
        return $routeFile;
    }

    /**
     * Generate route definition
     */
    protected function generateRoute(CrudConfiguration $config): string
    {
        $path = $config->getRoutePath();
        $name = $config->getRouteName();
        $component = $config->getComponentClass();
        
        $middleware = [];
        if ($config->auth) {
            $middleware[] = 'auth';
        }
        
        $middlewareStr = !empty($middleware) 
            ? "->middleware('" . implode("','", $middleware) . "')" 
            : '';
        
        return "Route::get('{$path}', {$component}::class)->name('{$name}'){$middlewareStr};";
    }

    /**
     * Check if route already exists
     */
    protected function routeExists(string $routeFile, CrudConfiguration $config): bool
    {
        if (!File::exists($routeFile)) {
            return false;
        }
        
        $content = File::get($routeFile);
        $name = $config->getRouteName();
        
        return Str::contains($content, "->name('{$name}')");
    }

    /**
     * Create route file with header
     */
    protected function createRouteFile(string $routeFile, bool $isFrontend = false): void
    {
        $type = $isFrontend ? 'Frontend' : 'Admin';
        $content = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n// Auto-generated {$type} CRUD routes\n";
        File::put($routeFile, $content);
    }

    /**
     * Remove route from file
     */
    public function remove(CrudConfiguration $config): bool
    {
        $routeFile = $this->getRouteFile($config);
        
        if (!File::exists($routeFile)) {
            return false;
        }
        
        $content = File::get($routeFile);
        $name = $config->getRouteName();
        
        // Remove the route line
        $lines = explode("\n", $content);
        $filtered = array_filter($lines, function($line) use ($name) {
            return !Str::contains($line, "->name('{$name}')");
        });
        
        File::put($routeFile, implode("\n", $filtered));
        
        return true;
    }

    /**
     * Get route file path for configuration
     */
    public function getPath(CrudConfiguration $config): string
    {
        return $this->getRouteFile($config);
    }
}
