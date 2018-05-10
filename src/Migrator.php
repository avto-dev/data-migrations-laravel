<?php

namespace AvtoDev\DataMigrationsLaravel;

use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\DatabaseManager;

class Migrator implements MigratorContract
{
    /**
     * Memory limit in safe mode.
     */
    const MIGRATE_SAFE_MEMORY = 1024 * 1024 * 63;

    /**
     * @var RepositoryContract
     */
    protected $repository;

    /**
     * @var SourceContract
     */
    protected $source;

    /**
     * @var DatabaseManager
     */
    protected $database;

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
        $this->database   = Container::getInstance()->make('db');
    }

    /**
     * {@inheritdoc}
     *
     * @return RepositoryContract
     */
    public function repository()
    {
        return $this->repository;
    }

    /**
     * {@inheritdoc}
     *
     * @return SourceContract
     */
    public function source()
    {
        return $this->source();
    }

    /**
     * {@inheritdoc}
     *
     * @param null $connection
     * @param bool $safe
     */
    public function migrate($connection = null, $safe = false)
    {
        if (! $this->repository()->repositoryExists()) {
            $this->repository()->createRepository();
        }

        $migrated = $this->repository()->getMigrations();

        if (empty($migrations = $this->source()->migrations($connection))) {
            return;
        }

        foreach ($migrations as $migration) {

            $migration_name = '';

            if ($this->source() instanceof Filesystem) {
                $migration_name = basename($migration, '.sql');
            } // Another source types

            if (! empty($migration_name) && ! in_array($migration_name, $migrated)) {
                $content = $this->source()->getContent($migration);

                $this->database->connection($connection)->statement($content);

                $this->repository()->insert($migration_name);
            }
        }
    }
}
