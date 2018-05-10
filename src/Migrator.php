<?php

namespace AvtoDev\DataMigrationsLaravel;

use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;

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

    protected function notMigrated()
    {
        $migrated               = $this->repository->migrations();
        $found_migrations       = $this->source->all();
        $found_migrations_names = array_flatten($found_migrations);

        $not_migrated = array_diff($found_migrations_names, $migrated);
    }

    public function migrate()
    {
        //dump($this->notMigrated());
    }

}
