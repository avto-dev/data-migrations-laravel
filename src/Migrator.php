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

    public function migrate()
    {
        //dump($this->notMigrated());
    }

    /**
     * {@inheritdoc}
     */
    public function notMigrated()
    {
        $migrated_names     = $this->repository->migrations();
        $found_migrations   = $this->source->all();
        $not_migrated_names = array_diff(array_flatten($found_migrations), $migrated_names);

        return array_filter(array_map(function (array $for_connection) use (&$not_migrated_names) {
            return array_filter($for_connection, function ($migrations_name) use (&$not_migrated_names) {
                return in_array($migrations_name, $not_migrated_names, true);
            });
        }, $found_migrations));
    }
}
