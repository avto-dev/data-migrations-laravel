<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel\Commands;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use AvtoDev\DataMigrationsLaravel\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;

class MigrateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'data-migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the data migrations';

    /**
     * Execute the console command.
     *
     * @param MigratorContract $migrator
     *
     * @return void
     */
    public function handle(MigratorContract $migrator): void
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        // Create migrations repository if needed
        if (! $migrator->repository()->repositoryExists()) {
            $this->call('data-migrate:install');
        }

        if (! empty($need_to_migrate = Arr::flatten($migrator->needToMigrateList()))) {
            /** @var ProgressBar|null $progress */
            $progress = null;

            $migrated = $migrator->migrate(
                $this->getConnectionName(),
                function ($migration_name, $status, $current, $total) use (&$progress) {
                    if (! ($progress instanceof ProgressBar)) {
                        $progress = $this->output->createProgressBar($total);
                        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message% (%estimated:-6s%)');
                        $progress->setMessage('In progress');
                    }

                    $this->updateProgressBar($progress, $migration_name, $status, $current, $total);
                }
            );

            if ($progress instanceof ProgressBar) {
                $progress->finish();

                $this->output->writeln('');
            }

            if (! empty($migrated)) {
                $this->info(
                    'Migrated:' . ($glue = PHP_EOL . ' ✔ ') . implode($glue, $migrated)
                );
            }
        } else {
            $this->comment('Nothing to migrate.');
        }
    }

    /**
     * @return string|null
     */
    protected function getConnectionName(): ?string
    {
        $connection_name = $this->option('connection');

        return \is_string($connection_name)
            ? $connection_name
            : null;
    }

    /**
     * Update interactive progress bar.
     *
     * @param ProgressBar $progress
     * @param string      $migration_name
     * @param string      $status
     * @param int         $current
     * @param int         $total
     *
     * @return void
     */
    protected function updateProgressBar(ProgressBar $progress, $migration_name, $status, $current, $total): void
    {
        switch ($status) {
            case Migrator::STATUS_MIGRATION_READ:
                $progress->setMessage("Read migration data for: <info>{$migration_name}</info>");
                break;

            case Migrator::STATUS_MIGRATION_STARTED:
                $progress->setMessage("Migration of <info>{$migration_name}</info> started");
                break;

            case Migrator::STATUS_MIGRATION_COMPLETED:
                $progress->setMessage("<info>{$migration_name}</info> migrated!");
                break;

            default:
                $progress->setMessage("Work with <info>{$migration_name}</info>");
        }

        $progress->setProgress($current);
        $progress->display();
    }

    /**
     * Get the console command options.
     *
     * @return mixed[][]
     */
    protected function getOptions(): array
    {
        return [
            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'Use only passed connection name.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
        ];
    }
}
