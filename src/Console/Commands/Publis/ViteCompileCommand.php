<?php

namespace Marufsharia\Hyro\Console\Commands\Publis;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ViteCompileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:vite-compile {--dev : Compile for development}
                                             {--watch : Watch for changes}
                                             {--prod : Compile for production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile Hyro package assets with Vite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Compiling Hyro assets with Vite...');

        $packagePath = realpath(__DIR__ . '/../../../../');

        // Check if package.json exists
        if (!file_exists($packagePath . '/package.json')) {
            $this->error('package.json not found in: ' . $packagePath);
            return 1;
        }

        // Check if Vite is installed
        if (!file_exists($packagePath . '/node_modules/vite')) {
            $this->info('Installing Vite dependencies...');

            $process = new Process(['npm', 'install'], $packagePath);
            $process->setTimeout(300);

            try {
                $process->mustRun();
                $this->info('Dependencies installed successfully.');
            } catch (ProcessFailedException $e) {
                $this->error('Failed to install dependencies: ' . $e->getMessage());
                return 1;
            }
        }

        // Determine command
        if ($this->option('watch')) {
            $command = ['npm', 'run', 'watch'];
        } elseif ($this->option('prod')) {
            $command = ['npm', 'run', 'build'];
        } else {
            $command = ['npm', 'run', 'dev'];
        }

        $this->info('Running: ' . implode(' ', $command));

        $process = new Process($command, $packagePath);
        $process->setTimeout(600);

        try {
            $process->mustRun(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->error($buffer);
                } else {
                    $this->line($buffer);
                }
            });

            $this->info('Assets compiled successfully with Vite!');

            // Show compiled assets
            $buildDir = $packagePath . '/public/build';
            if (file_exists($buildDir)) {
                $this->info('Compiled assets in: ' . $buildDir);

                $files = glob($buildDir . '/assets/*/*');
                foreach ($files as $file) {
                    $this->line('- ' . basename($file));
                }
            }

        } catch (ProcessFailedException $e) {
            $this->error('Vite compilation failed: ' . $e->getMessage());

            // Check for common issues
            $this->checkViteIssues($packagePath);

            return 1;
        }

        return 0;
    }

    /**
     * Check for common Vite issues.
     */
    protected function checkViteIssues($packagePath)
    {
        $this->warn('Checking for common Vite issues...');

        // Check Node.js version
        $process = new Process(['node', '--version'], $packagePath);
        try {
            $process->mustRun();
            $nodeVersion = trim($process->getOutput());
            $this->info('Node.js version: ' . $nodeVersion);

            // Check if Node.js version is compatible (>= 18)
            $majorVersion = (int) explode('.', $nodeVersion)[0];
            if ($majorVersion < 18) {
                $this->error('Vite requires Node.js 18 or higher. Current version: ' . $nodeVersion);
            }
        } catch (ProcessFailedException $e) {
            $this->error('Node.js not found or not working properly');
        }

        // Check if vite.config.js exists
        if (!file_exists($packagePath . '/vite.config.js')) {
            $this->error('vite.config.js not found in package directory');
        }

        // Check if entry files exist
        if (!file_exists($packagePath . '/resources/css/hyro.css')) {
            $this->error('CSS entry file not found: resources/css/hyro.css');
        }

        if (!file_exists($packagePath . '/resources/js/hyro.js')) {
            $this->error('JS entry file not found: resources/js/hyro.js');
        }

        $this->info('Common checks completed.');
    }
}
