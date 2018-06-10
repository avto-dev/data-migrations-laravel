<?php

declare(strict_types = 1);

namespace AvtoDev\DataMigrationsLaravel\Commands;

use Exception;
use RuntimeException;
use Illuminate\Console\Command;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'data-migrate:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the data migrations repository';

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
        if ($repository->repositoryExists()) {
            $this->comment('Repository already exists in your database');

            return;
        }

        try {
            $repository->createRepository();

            if ($repository->repositoryExists()) {
                $this->info('Repository created successfully!');
            }
        } catch (Exception $e) {
            throw new RuntimeException(
                'Cannot create repository in your database. ' . $e->getMessage(), $e->getCode(), $e
            );
        }
    }
}
