<?php

namespace Marufsharia\Hyro\Console\Commands\Plugin\Concerns;

use Illuminate\Support\Str;

/**
 * Trait PluginResolver
 *
 * Provides flexible plugin identification across different naming formats:
 * - Kebab case: a-b-c-test
 * - Pascal case: ABCTest
 * - Snake case: abc_test
 * - Lower case: abctest
 * - Plugin name vs ID matching
 */
trait PluginResolver
{
    /**
     * Resolve a plugin ID from various input formats
     *
     * @param string $input User input (can be any format)
     * @param mixed $pluginManager Plugin manager instance
     * @return string|null Resolved plugin ID or null if not found
     */
    protected function resolvePluginId(string $input, $pluginManager): ?string
    {
        $plugins = $pluginManager->getAllPlugins();

        // Strategy 1: Exact match on plugin ID
        if ($plugins->has($input)) {
            return $input;
        }

        // Strategy 2: Exact match on plugin name (from meta)
        foreach ($plugins as $id => $data) {
            $pluginName = $data['meta']['name'] ?? null;
            if ($pluginName && $pluginName === $input) {
                return $id;
            }
        }

        // Strategy 3: Case-insensitive match on plugin ID
        $inputLower = strtolower($input);
        foreach ($plugins as $id => $data) {
            if (strtolower($id) === $inputLower) {
                return $id;
            }
        }

        // Strategy 4: Case-insensitive match on plugin name
        foreach ($plugins as $id => $data) {
            $pluginName = $data['meta']['name'] ?? null;
            if ($pluginName && strtolower($pluginName) === $inputLower) {
                return $id;
            }
        }

        // Strategy 5: Normalize to kebab-case and match
        $inputKebab = Str::kebab($input);
        if ($plugins->has($inputKebab)) {
            return $inputKebab;
        }

        // Strategy 6: Normalize to snake_case and match
        $inputSnake = Str::snake($input);
        foreach ($plugins as $id => $data) {
            if (Str::snake($id) === $inputSnake) {
                return $id;
            }
        }

        // Strategy 7: Remove all separators and compare alphanumerically
        $cleanInput = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($input));

        foreach ($plugins as $id => $data) {
            // Check against plugin ID
            $cleanId = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($id));
            if ($cleanInput === $cleanId) {
                return $id;
            }

            // Check against plugin name
            $pluginName = $data['meta']['name'] ?? null;
            if ($pluginName) {
                $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($pluginName));
                if ($cleanInput === $cleanName) {
                    return $id;
                }
            }
        }

        // Strategy 8: Partial match (if input is contained in plugin name or ID)
        $matches = [];
        foreach ($plugins as $id => $data) {
            $pluginName = $data['meta']['name'] ?? $id;

            // Check if input is part of the ID or name
            if (stripos($id, $input) !== false || stripos($pluginName, $input) !== false) {
                $matches[$id] = $pluginName;
            }
        }

        // If exactly one partial match, return it
        if (count($matches) === 1) {
            return array_key_first($matches);
        }

        // If multiple matches, show them to the user (handled by caller)
        if (count($matches) > 1) {
            $this->handleMultipleMatches($input, $matches);
            return null;
        }

        return null;
    }

    /**
     * Handle case where multiple plugins match the input
     */
    protected function handleMultipleMatches(string $input, array $matches): void
    {
        $this->components->warn("Multiple plugins match '{$input}':");
        $this->newLine();

        foreach ($matches as $id => $name) {
            $this->line("  - {$name} (ID: {$id})");
        }

        $this->newLine();
        $this->components->info("Please specify the exact plugin ID:");
        foreach (array_keys($matches) as $id) {
            $this->line("  php artisan {$this->getName()} {$id}");
        }
    }

    /**
     * Find plugin with helpful error messages
     */
    protected function findPlugin(string $input, $pluginManager): ?string
    {
        $pluginId = $this->resolvePluginId($input, $pluginManager);

        if (!$pluginId) {
            $this->components->error("❌ Plugin '{$input}' not found.");
            $this->displayPluginSuggestions($input, $pluginManager);
        }

        return $pluginId;
    }

    /**
     * Display helpful suggestions when plugin is not found
     */
    protected function displayPluginSuggestions(string $input, $pluginManager): void
    {
        $this->newLine();
        $this->components->info("💡 Did you mean one of these?");
        $this->newLine();

        $plugins = $pluginManager->getAllPlugins();

        if ($plugins->isEmpty()) {
            $this->line("  No plugins available.");
            $this->newLine();
            $this->comment("Run: php artisan hyro:plugin:make YourPluginName");
            return;
        }

        // Find similar plugins using Levenshtein distance
        $suggestions = [];
        foreach ($plugins as $id => $data) {
            $name = $data['meta']['name'] ?? $id;

            $distanceToId = levenshtein(strtolower($input), strtolower($id));
            $distanceToName = levenshtein(strtolower($input), strtolower($name));

            $minDistance = min($distanceToId, $distanceToName);

            if ($minDistance <= 5) { // Only suggest if reasonably close
                $suggestions[$id] = [
                    'name' => $name,
                    'distance' => $minDistance
                ];
            }
        }

        // Sort by distance
        uasort($suggestions, fn($a, $b) => $a['distance'] <=> $b['distance']);

        if (!empty($suggestions)) {
            foreach (array_slice($suggestions, 0, 5) as $id => $data) {
                $this->line("  • {$data['name']} (ID: {$id})");
            }
        } else {
            // Just show all available plugins
            $this->line("  Available plugins:");
            foreach ($plugins->take(10) as $id => $data) {
                $name = $data['meta']['name'] ?? $id;
                $this->line("  • {$name} (ID: {$id})");
            }
        }

        $this->newLine();
        $this->comment("List all plugins: php artisan hyro:plugin:list");
    }

    /**
     * Resolve and validate plugin exists
     */
    protected function resolveAndValidatePlugin(string $input, $pluginManager): ?array
    {
        $pluginId = $this->resolvePluginId($input, $pluginManager);

        if (!$pluginId) {
            $this->components->error("❌ Plugin '{$input}' not found.");
            $this->displayPluginSuggestions($input, $pluginManager);
            return null;
        }

        return [
            'id' => $pluginId,
            'data' => $pluginManager->getAllPlugins()->get($pluginId)
        ];
    }
}
