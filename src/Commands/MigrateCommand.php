<?php

namespace AvtoDev\DataMigrationsLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputOption;
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
    protected $description = 'Run the database migrations';

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
            $this->comment(
                'Migrating next migrations:' . ($glue = PHP_EOL . ' ➤ ') . implode($glue, $need_to_migrate)
            );

            $migrated = $migrator->migrate($this->option('connection'));

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
