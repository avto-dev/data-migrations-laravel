<?php

namespace AvtoDev\DataMigrationsLaravel\Commands;

use AvtoDev\DataMigrationsLaravel\Contracts\DataMigrationsRepositoryContract;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Class DataMigrationsTableCommand.
 *
 * Command for crating migration file for data migrations.
 */
class DataMigrationsTableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'data-migrations:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the data migration repository';

    /**
     * Execute the console command.
     *
     * @param DataMigrationsRepositoryContract $repository
     *
     * @return int
     *
     * @throws RuntimeException
     */
    public function handle(DataMigrationsRepositoryContract $repository)
    {
        if ($repository->repositoryExists()) {
            $this->comment('Repository already exists in your database');

            return 0;
        }

        if ($repository->createRepository() && $repository->repositoryExists()) {
            $this->info('Repository created successfully!');

            return 0;
        }

        throw new RuntimeException('Cannot create repository in your database');
    }
}
