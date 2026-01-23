<?php

namespace Marufsharia\Hyro\Console\Commands\Publis;

use Illuminate\Console\Command;

class HyroPublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:publish {--assets : Publish compiled assets}
                                          {--views : Publish views}
                                          {--config : Publish config}
                                          {--migrations : Publish migrations}
                                          {--source : Publish source assets}
                                          {--all : Publish everything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Hyro package assets and resources';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ($this->option('all')) {
            $this->publishAll();
            return;
        }

        $tags = [];

        if ($this->option('assets')) {
            $tags[] = 'hyro-assets';
        }

        if ($this->option('views')) {
            $tags[] = 'hyro-views';
        }

        if ($this->option('config')) {
            $tags[] = 'hyro-config';
        }

        if ($this->option('migrations')) {
            $tags[] = 'hyro-migrations';
        }

        if ($this->option('source')) {
            $tags[] = 'hyro-source-assets';
        }

        if (empty($tags)) {
            $choice = $this->choice('What do you want to publish?', [
                'all' => 'Everything',
                'assets' => 'Compiled assets only',
                'views' => 'Views only',
                'config' => 'Config only',
                'migrations' => 'Migrations only',
                'source' => 'Source assets only',
            ], 'all');

            if ($choice === 'all') {
                $this->publishAll();
                return;
            }

            $tags[] = "hyro-{$choice}";
        }

        foreach ($tags as $tag) {
            $this->call('vendor:publish', ['--tag' => $tag]);
        }

        $this->info('Published successfully!');
    }

    /**
     * Publish all Hyro resources.
     */
    protected function publishAll(): void
    {
        $tags = [
            'hyro-config',
            'hyro-migrations',
            'hyro-views',
            'hyro-assets',
            'hyro-source-assets',
        ];

        foreach ($tags as $tag) {
            $this->call('vendor:publish', ['--tag' => $tag]);
        }

        $this->info('All Hyro resources published successfully!');
    }
}
