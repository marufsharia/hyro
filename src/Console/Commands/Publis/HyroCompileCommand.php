<?php

namespace Marufsharia\Hyro\Console\Commands\Publis;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class HyroCompileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:compile {--dev : Compile for development}
                                         {--watch : Watch for changes}
                                         {--prod : Compile for production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile Hyro package assets';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $packagePath = base_path('packages/marufsharia/hyro');

        if (!file_exists($packagePath . '/package.json')) {
            $this->error('Package.json not found! Make sure you are in the package directory.');
            return;
        }

        $command = $this->option('prod') ? 'npm run prod' : ($this->option('watch') ? 'npm run watch' : 'npm run dev');

        $this->info("Compiling Hyro assets with: {$command}");

        $process = new Process([$command], $packagePath);
        $process->setTimeout(3600);
        $process->start();

        foreach ($process as $type => $data) {
            echo $data;
        }

        if ($process->isSuccessful()) {
            $this->info('Assets compiled successfully!');

            // Copy compiled assets to public directory
            $this->copyCompiledAssets($packagePath);
        } else {
            $this->error('Compilation failed!');
        }
    }

    /**
     * Copy compiled assets to package public directory.
     */
    protected function copyCompiledAssets(string $packagePath): void
    {
        $sourceCss = $packagePath . '/public/css/hyro.css';
        $sourceJs = $packagePath . '/public/js/hyro.js';

        if (file_exists($sourceCss) && file_exists($sourceJs)) {
            $this->info('Compiled assets are ready in: ' . $packagePath . '/public/');
        } else {
            $this->warn('Compiled assets not found! Check webpack.mix.js configuration.');
        }
    }
}
