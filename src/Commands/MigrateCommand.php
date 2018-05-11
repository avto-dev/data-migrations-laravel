<?php

namespace AvtoDev\DataMigrationsLaravel\Commands;

use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputOption;

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

        $connection_name = $this->hasOption($connection_option_name = 'connection')
            ? trim($this->option($connection_option_name))
            : null;

        if (! empty($need_to_migrate = array_flatten($migrator->needToMigrateList()))) {
            $this->comment('Migrating next migrations:' . implode(PHP_EOL . ' ➤ ', $need_to_migrate));

            $migrated = $migrator->migrate($connection_name);

            $this->info('Migrated:' . implode(PHP_EOL . ' ✔ ', $migrated));
        } else {
            $this->comment('Nothing to migrate');
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
