<?php

namespace Marufsharia\Hyro\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Marufsharia\Hyro\Exceptions\CommandValidationException;
use Marufsharia\Hyro\Models\AuditLog;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputOption; // Add this import

abstract class BaseCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature;

    /**
     * The command description.
     */
    protected $description;

    /**
     * Whether to run in dry-run mode.
     */
    protected bool $dryRun = false;

    /**
     * Whether to force execution without confirmation.
     */
    protected bool $force = false;

    /**
     * Command execution batch ID for auditing.
     */
    protected ?string $batchId = null;

    /**
     * Statistics for command execution.
     */
    protected array $stats = [
        'processed' => 0,
        'succeeded' => 0,
        'failed' => 0,
        'warnings' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->batchId = uniqid('hyro_cli_', true);

        try {
            $this->bootstrap();
            $this->validatePreconditions();
            $this->executeCommand();
            $this->finalize();

            return $this->stats['failed'] > 0
                ? SymfonyCommand::FAILURE
                : SymfonyCommand::SUCCESS;
        } catch (CommandValidationException $e) {
            $this->error("Validation failed: {$e->getMessage()}");
            return SymfonyCommand::INVALID;
        } catch (\Exception $e) {
            $this->logError($e);
            $this->error("Command failed: {$e->getMessage()}");
            return SymfonyCommand::FAILURE;
        }
    }

    /**
     * Bootstrap the command.
     */
    protected function bootstrap(): void
    {
        // Safely get options (check if they exist first)
        $this->dryRun = $this->hasOption('dry-run') ? $this->option('dry-run') : false;
        $this->force = $this->hasOption('force') ? $this->option('force') : false;

        if ($this->dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->registerShutdownHandler();
    }

    /**
     * Validate command preconditions.
     */
    protected function validatePreconditions(): void
    {
        // Check if CLI is enabled
        if (!Config::get('hyro.cli.enabled', true)) {
            throw new CommandValidationException(
                'Hyro CLI is disabled. Enable it in config/hyro.php or set HYRO_CLI_ENABLED=true'
            );
        }

        // Check if the command requires a specific environment
        if ($this->isProductionCommand() && app()->environment('production') && !$this->force) {
            if (!$this->confirm('⚠️  This command is destructive and you are in production. Continue?')) {
                throw new CommandValidationException('Command aborted by users');
            }
        }
    }

    /**
     * Execute the main command logic.
     */
    abstract protected function executeCommand(): void;

    /**
     * Finalize command execution.
     */
    protected function finalize(): void
    {
        $this->logAudit();
        $this->displayStats();

        if ($this->dryRun) {
            $this->info('✅ Dry run completed successfully');
        } else {
            $this->info('✅ Command completed successfully');
        }
    }

    /**
     * Check if this is a production-sensitive command.
     */
    protected function isProductionCommand(): bool
    {
        return in_array($this->getName(), [
            'hyro:emergency:revoke-all-tokens',
            'hyro:emergency:lockdown',
            'hyro:role:delete',
            'hyro:privilege:delete',
            'hyro:users:delete-all-tokens',
        ]);
    }

    /**
     * Register a shutdown handler for cleanup.
     */
    protected function registerShutdownHandler(): void
    {
        register_shutdown_function(function () {
            if ($this->stats['failed'] > 0 && !$this->dryRun) {
                $this->warn('⚠️  Command completed with errors. Some operations may have failed.');
            }
        });
    }

    /**
     * Log an error.
     */
    protected function logError(\Exception $e): void
    {
        $this->stats['failed']++;

        if (Config::get('hyro.auditing.enabled', true)) {
            AuditLog::log('cli_command_failed', null, null, [
                'command' => $this->getName(),
                'arguments' => $this->arguments(),
                'options' => $this->options(),
                'error' => $e->getMessage(),
                'trace' => $this->shouldLogTrace() ? $e->getTraceAsString() : null,
                'batch_id' => $this->batchId,
            ], [
                'tags' => ['cli', 'error', $this->getName()],
            ]);
        }
    }

    /**
     * Log command audit.
     */
    protected function logAudit(): void
    {
        if (!Config::get('hyro.auditing.enabled', true) || $this->dryRun) {
            return;
        }

        AuditLog::log('cli_command_executed', null, null, [
            'command' => $this->getName(),
            'arguments' => $this->arguments(),
            'options' => $this->options(),
            'stats' => $this->stats,
            'dry_run' => $this->dryRun,
            'batch_id' => $this->batchId,
        ], [
            'tags' => ['cli', 'command', $this->getName()],
        ]);
    }

    /**
     * Display command statistics.
     */
    protected function displayStats(): void
    {
        if ($this->stats['processed'] > 0) {
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Processed', $this->stats['processed']],
                    ['Succeeded', $this->stats['succeeded']],
                    ['Failed', $this->stats['failed']],
                    ['Warnings', $this->stats['warnings']],
                ]
            );
        }
    }

    /**
     * Should log full trace for errors.
     */
    protected function shouldLogTrace(): bool
    {
        return Config::get('hyro.cli.log_trace_on_error', false) || app()->environment('local');
    }

    /**
     * Get common options for all commands.
     */
    protected function getCommonOptions(): array
    {
        return [
            ['dry-run', null, InputOption::VALUE_NONE, 'Preview changes without applying them'],
            ['force', 'f', InputOption::VALUE_NONE, 'Skip confirmation prompts'],
            ['verbose', 'v', InputOption::VALUE_NONE, 'Show detailed output'],
        ];
    }

    /**
     * Confirm destructive action with users.
     */
    protected function confirmDestructiveAction(string $message): bool
    {
        $hasForceOption = $this->hasOption('force');
        $hasDryRunOption = $this->hasOption('dry-run');

        if (($hasForceOption && $this->force) || ($hasDryRunOption && $this->dryRun)) {
            return true;
        }

        return $this->confirm($message, false);
    }

    /**
     * Execute within a transaction if not in dry-run mode.
     */
    protected function executeInTransaction(callable $callback): mixed
    {
        if ($this->dryRun) {
            return $callback();
        }

        return DB::transaction(function () use ($callback) {
            try {
                $result = $callback();
                $this->stats['succeeded']++;
                return $result;
            } catch (\Exception $e) {
                $this->stats['failed']++;
                throw $e;
            }
        });
    }

    /**
     * Validate input against rules.
     */
    protected function validateInput(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            throw new CommandValidationException(implode(', ', $errors));
        }

        return $validator->validated();
    }

    /**
     * Find a users by identifier (email, ID, or username).
     */
    protected function findUser(string $identifier): ?object
    {
        $userModel = Config::get('hyro.models.users');

        return $userModel::where('email', $identifier)
            ->orWhere('id', $identifier)
            ->orWhere('username', $identifier)
            ->first();
    }

    /**
     * Find a role by slug or ID.
     */
    protected function findRole(string $identifier): ?object
    {
        $roleModel = Config::get('hyro.models.role');

        return $roleModel::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->first();
    }

    /**
     * Find a privilege by slug or ID.
     */
    protected function findPrivilege(string $identifier): ?object
    {
        $privilegeModel = Config::get('hyro.models.privilege');

        return $privilegeModel::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->first();
    }

    /**
     * Display a progress bar for batch operations.
     */
    public function withItemsProgressBar(iterable $items, callable $callback): void
    {
        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            try {
                $callback($item);
                $this->stats['processed']++;
            } catch (\Exception $e) {
                $this->stats['failed']++;
                $this->warn("Failed processing item: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
