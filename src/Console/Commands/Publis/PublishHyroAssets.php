<?php

namespace Marufsharia\Hyro\Console\Commands\Publis;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishHyroAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:publish-assets
                            {--force : Overwrite existing files}
                            {--views : Publish views only}
                            {--assets : Publish compiled assets only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Hyro admin UI assets and views';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('views') || !$this->option('assets')) {
            $this->publishViews();
        }

        if ($this->option('assets') || !$this->option('views')) {
            $this->publishAssets();
        }

        $this->info('Hyro assets published successfully.');

        return Command::SUCCESS;
    }

    /**
     * Publish admin views.
     *
     * @return void
     */
    protected function publishViews()
    {
        $viewsPath = package_path('resources/views/admin');
        $targetPath = resource_path('views/vendor/hyro/admin');

        $this->ensureDirectoryExists($targetPath);

        if ($this->option('force') || !File::exists($targetPath . '/index.blade.php')) {
            File::copyDirectory($viewsPath, $targetPath);
            $this->info('Views published to: ' . $targetPath);
        } else {
            $this->warn('Views already exist. Use --force to overwrite.');
        }
    }

    /**
     * Publish compiled assets.
     *
     * @return void
     */
    protected function publishAssets()
    {
        $assetsPath = package_path('public');
        $targetPath = public_path('vendor/hyro');

        $this->ensureDirectoryExists($targetPath);

        if ($this->option('force') || !File::exists($targetPath . '/hyro.css')) {
            File::copyDirectory($assetsPath, $targetPath);
            $this->info('Assets published to: ' . $targetPath);
        } else {
            $this->warn('Assets already exist. Use --force to overwrite.');
        }
    }

    /**
     * Ensure directory exists.
     *
     * @param  string  $path
     * @return void
     */
    protected function ensureDirectoryExists(string $path)
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}
