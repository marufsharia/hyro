<?php

namespace Marufsharia\Hyro\AdminPanel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    protected $signature = 'hyro:publish-assets {--force : Overwrite existing files}';

    protected $description = 'Publish Hyro admin panel assets to public directory';

    public function handle()
    {
        $this->info('Publishing Hyro assets...');
        $this->newLine();

        $force = $this->option('force');

        // First, publish the built assets (manifest.json and compiled assets)
        $buildSource = __DIR__ . '/../../../public/build';
        $buildDestination = public_path('vendor/hyro');
        
        if (File::exists($buildSource)) {
            $this->info('Publishing built assets (Vite compiled)...');
            $this->publishDirectory($buildSource, $buildDestination, 'built', $force, true);
        } else {
            $this->warn('⚠ Built assets not found. Run "npm run build" in the package directory first.');
        }

        // Then publish raw CSS/JS as fallback
        $sources = [
            'css' => [
                'source' => __DIR__ . '/../../../resources/css',
                'destination' => public_path('vendor/hyro/css'),
            ],
            'js' => [
                'source' => __DIR__ . '/../../../resources/js',
                'destination' => public_path('vendor/hyro/js'),
            ],
        ];

        $this->newLine();
        $this->info('Publishing raw assets (fallback)...');
        foreach ($sources as $type => $paths) {
            $this->publishDirectory($paths['source'], $paths['destination'], $type, $force);
        }

        $this->newLine();
        $this->info('✓ Assets published successfully!');
        $this->newLine();
        $this->info('Assets published to:');
        $this->line('  • public/vendor/hyro/manifest.json (Vite manifest)');
        $this->line('  • public/vendor/hyro/assets/ (compiled CSS/JS)');
        $this->line('  • public/vendor/hyro/css/ (raw CSS fallback)');
        $this->line('  • public/vendor/hyro/js/ (raw JS fallback)');

        return 0;
    }

    private function publishDirectory($source, $destination, $type, $force, $recursive = false)
    {
        if (!File::exists($source)) {
            $this->warn("⚠ Source directory not found: {$source}");
            return;
        }

        // Create destination directory if it doesn't exist
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        if ($recursive) {
            // Copy entire directory recursively (for build folder)
            if ($force || !File::exists($destination . '/manifest.json')) {
                File::copyDirectory($source, $destination);
                $this->line("✓ Copied {$type} directory recursively");
            } else {
                $this->line("  Skipped {$type} directory (use --force to overwrite)");
            }
            return;
        }

        // Copy files (non-recursive)
        $files = File::files($source);
        $copied = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $destinationFile = $destination . '/' . $file->getFilename();

            if (File::exists($destinationFile) && !$force) {
                $skipped++;
                continue;
            }

            File::copy($file->getPathname(), $destinationFile);
            $copied++;
        }

        if ($copied > 0) {
            $this->line("✓ Copied {$copied} {$type} file(s)");
        }

        if ($skipped > 0) {
            $this->line("  Skipped {$skipped} existing file(s) (use --force to overwrite)");
        }
    }
}
