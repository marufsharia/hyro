<?php

namespace Marufsharia\Hyro\Console\Commands\Publis;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ViteDevCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:vite-dev {--host= : Dev server host}
                                         {--port= : Dev server port}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Vite dev server for Hyro package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Vite dev server for Hyro...');

        $packagePath = realpath(__DIR__ . '/../../../../');

        // Check if Vite is installed
        if (!file_exists($packagePath . '/node_modules/vite')) {
            $this->error('Vite not found. Please run: npm install');
            return 1;
        }

        // Build command
        $command = ['npm', 'run', 'dev'];

        if ($this->option('host')) {
            $command = array_merge($command, ['--', '--host', $this->option('host')]);
        }

        if ($this->option('port')) {
            $command = array_merge($command, ['--', '--port', $this->option('port')]);
        }

        $this->info('Running: ' . implode(' ', $command));

        // Create hot file for Laravel Vite integration
        $this->createHotFile($packagePath);

        $process = new Process($command, $packagePath);
        $process->setTimeout(0); // No timeout for dev server

        $this->info('Vite dev server starting...');
        $this->info('Press Ctrl+C to stop the server');

        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            $this->error('Vite dev server failed: ' . $e->getMessage());

            // Clean up hot file
            $hotFile = $packagePath . '/public/hot';
            if (file_exists($hotFile)) {
                unlink($hotFile);
            }

            return 1;
        }

        return 0;
    }

    /**
     * Create hot file for Laravel Vite integration.
     */
    protected function createHotFile($packagePath)
    {
        $hotFile = $packagePath . '/public/hot';

        // Default Vite dev server URL
        $host = $this->option('host') ?: 'localhost';
        $port = $this->option('port') ?: '5173';

        $url = "http://{$host}:{$port}";

        file_put_contents($hotFile, $url);

        $this->info("Hot file created at: {$hotFile}");
        $this->info("Dev server URL: {$url}");
    }
}
