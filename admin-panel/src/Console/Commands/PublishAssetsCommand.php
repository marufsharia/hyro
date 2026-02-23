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

        // Define source and destination paths
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

        foreach ($sources as $type => $paths) {
            $this->publishDirectory($paths['source'], $paths['destination'], $type, $force);
        }

        $this->newLine();
        $this->info('✓ Assets published successfully!');
        $this->newLine();
        $this->info('Assets published to:');
        $this->line('  • public/vendor/hyro/css');
        $this->line('  • public/vendor/hyro/js');

        return 0;
    }

    private function publishDirectory($source, $destination, $type, $force)
    {
        if (!File::exists($source)) {
            $this->warn("⚠ Source directory not found: {$source}");
            return;
        }

        // Create destination directory if it doesn't exist
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        // Copy files
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
