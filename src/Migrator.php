<?php

namespace AvtoDev\DataMigrationsLaravel;

use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use AvtoDev\DataMigrationsLaravel\Sources\MigrationNameIsFilePathInterface;

class Migrator implements MigratorContract
{
    /**
     * @var RepositoryContract
     */
    protected $repository;

    /**
     * @var SourceContract
     */
    protected $source;

    /**
     * Migrator constructor.
     *
     * @param RepositoryContract $repository
     * @param SourceContract     $source
     */
    public function __construct(RepositoryContract $repository, SourceContract $source)
    {
        $this->repository = $repository;
        $this->source     = $source;
    }

    /**
     * Get migrations repository instance.
     *
     * @return RepositoryContract
     */
    public function repository()
    {
        return $this->repository;
    }

    /**
     * Get the migrations source instance.
     *
     * @return SourceContract
     */
    public function source()
    {
        return $this->source;
    }

    public function migrate()
    {
        $migrated     = $this->repository->getMigrations();
        $not_migrated = $this->getSourceMigrations();

        if ($this->source instanceof MigrationNameIsFilePathInterface) {
            $not_migrated = array_map(function ($migration_path) {
                return pathinfo(basename($migration_path), PATHINFO_FILENAME);
            }, $not_migrated);
        }

        return $not_migrated;
    }

    /**
     * Returns an array of storage migrations.
     *
     * @return string[]
     */
    protected function getSourceMigrations()
    {
        $migrations = [];

        foreach (array_merge($this->source->connections(), [null]) as $connection) {
            $migrations[] = $this->source->migrations($connection);
        }

        return array_flatten($migrations);
    }
}
