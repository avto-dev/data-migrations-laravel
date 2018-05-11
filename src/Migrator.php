<?php

namespace AvtoDev\DataMigrationsLaravel;

use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
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
     * @var ExecutorContract
     */
    protected $executor;

    /**
     * Migrator constructor.
     *
     * @param RepositoryContract $repository
     * @param SourceContract     $source
     * @param ExecutorContract   $executor
     */
    public function __construct(RepositoryContract $repository, SourceContract $source, ExecutorContract $executor)
    {
        $this->repository = $repository;
        $this->source     = $source;
        $this->executor   = $executor;
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

    public function migrate($connection_name = null)
    {
        $migrated = [];

        if (! $this->repository->repositoryExists()) {
            $this->repository->createRepository();
        }

        if (! empty($all_migrations = array_flatten($not_migrated = $this->notMigrated()))) {
            // Leave only passed connection name, if passed
            if ($connection_name !== null) {
                $not_migrated = array_filter($not_migrated, function ($not_migrated_connection) use ($connection_name) {
                    return $not_migrated_connection !== $connection_name;
                }, ARRAY_FILTER_USE_KEY);
            }

            foreach ($not_migrated as $migrations_connection_name => $migrations_names) {
                foreach ((array) $migrations_names as $migration_name) {
                    // Convert empty key name (used for default connection) into null
                    $migrations_connection_name = empty($migrations_connection_name)
                        ? null
                        : $migrations_connection_name;

                    $migration_data = $this->source->get($migration_name, $migrations_connection_name);

                    if ($this->executor->execute($migration_data, $migrations_connection_name)) {
                        $migrated[] = $migration_name;
                        $this->repository->insert($migration_name);
                    }
                }
            }
        }

        return $migrated;
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
