<?php

namespace AvtoDev\DataMigrationsLaravel\Commands;

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
    public function handle(MigratorContract $migrator)
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        // Create migrations repository if needed
        if (! $migrator->repository()->repositoryExists()) {
            $this->call('data-migrate:install');
        }

        if (! empty($need_to_migrate = array_flatten($migrator->needToMigrateList()))) {
            /** @var ProgressBar|null $progress */
            $progress = null;

            $migrator->migrate(
                $this->option('connection'),
                function ($migration_name, $status, $current, $total) use (&$progress) {
                    if (! ($progress instanceof ProgressBar)) {
                        $progress = $this->output->createProgressBar($total);
                        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message% (%estimated:-6s%)');
                        $progress->setMessage('In progress');
                        $progress->display();
                    }

                    switch ($status) {
                        case Migrator::STATUS_MIGRATION_READ:
                            $progress->setMessage("Read migration data for: <info>{$migration_name}</info>");
                            break;

                        case Migrator::STATUS_MIGRATION_STARTED:
                            $progress->setMessage("Migration <info>{$migration_name}</info> started");
                            break;

                        case Migrator::STATUS_MIGRATION_COMPLETED:
                            $progress->setMessage("Migration <info>{$migration_name}</info> migrated");
                            break;

                        default:
                            $progress->setMessage($migration_name);
                    }

                    $progress->setProgress($current);
                }
            );

            if ($progress instanceof ProgressBar) {
                $progress->finish();
            }
        } else {
            $this->comment('Nothing to migrate.');
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'Use only passed connection name.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
        ];
    }
}
