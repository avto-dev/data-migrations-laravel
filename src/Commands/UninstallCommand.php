<?php

namespace AvtoDev\DataMigrationsLaravel\Commands;

use Exception;
use RuntimeException;
use Illuminate\Console\Command;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class UninstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'data-migrate:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the data migrations repository';

    /**
     * Execute the console command.
     *
     * @param RepositoryContract $repository
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function handle(RepositoryContract $repository)
    {
        if (! $repository->repositoryExists()) {
            $this->comment('Repository already not exists in your database');

            return;
        }

        try {
            $repository->deleteRepository();

            if (! $repository->repositoryExists()) {
                $this->info('Repository removed successfully!');
            }
        } catch (Exception $e) {
            throw new RuntimeException(
                'Cannot remove repository in your database. ' . $e->getMessage(), $e->getCode(), $e
            );
        }
    }
}
