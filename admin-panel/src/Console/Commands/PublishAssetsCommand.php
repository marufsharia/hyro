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

        // Publish the built assets from dist directory
        $distSource = __DIR__ . '/../../../dist';
        $distDestination = public_path('vendor/hyro');
        
        if (File::exists($distSource)) {
            $this->info('Publishing built assets from dist directory...');
            $this->publishDirectory($distSource, $distDestination, 'dist', $force, true);
        } else {
            $this->warn('⚠ Built assets not found in dist directory.');
            $this->warn('  Run "npm run build" in the package directory first.');
            $this->newLine();
            $this->info('Falling back to CDN assets...');
            $this->line('  The package will automatically use CDN fallbacks for Tailwind CSS and Alpine.js');
        }

        $this->newLine();
        $this->info('✓ Asset publishing complete!');
        
        if (File::exists($distDestination . '/manifest.json')) {
            $this->newLine();
            $this->info('Assets published to:');
            $this->line('  • public/vendor/hyro/manifest.json (Vite manifest)');
            $this->line('  • public/vendor/hyro/css/ (compiled CSS)');
            $this->line('  • public/vendor/hyro/js/ (compiled JS)');
        }

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
