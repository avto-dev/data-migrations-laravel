<?php

declare(strict_types = 1);

namespace AvtoDev\DataMigrationsLaravel;

use Closure;
use Illuminate\Support\Arr;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class Migrator implements MigratorContract
{
    /**
     * Migration statuses (for process interacting at first).
     */
    public const STATUS_MIGRATION_STARTED   = 'migration_started';

    public const STATUS_MIGRATION_COMPLETED = 'migration_completed';

    public const STATUS_MIGRATION_READ      = 'migration_read';

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
     * {@inheritdoc}
     */
    public function repository(): RepositoryContract
    {
        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function source(): SourceContract
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(?string $connection_name = null, ?Closure $migrating_closure = null): array
    {
        $migrated     = [];
        $not_migrated = $this->needToMigrateList();

        if (! empty($all_migrations = Arr::flatten($not_migrated))) {
            // Leave only passed connection name, if passed
            if ($connection_name !== null) {
                $not_migrated = \array_filter($not_migrated,
                    static function (?string $not_migrated_connection) use ($connection_name) {
                        return $not_migrated_connection === $connection_name;
                    }, \ARRAY_FILTER_USE_KEY
                );
            }

            $total   = \count($all_migrations);
            $current = 1;

            foreach ($not_migrated as $migrations_connection_name => $migrations_names) {
                foreach ((array) $migrations_names as $migration_name) {
                    // Convert empty key name (used for default connection) into null
                    $migrations_connection_name = empty($migrations_connection_name)
                        ? null
                        : (string) $migrations_connection_name;

                    if (\is_callable($migrating_closure)) {
                        $migrating_closure($migration_name, static::STATUS_MIGRATION_READ, $current, $total);
                    }

                    $migration_data = $this->source->get($migration_name, $migrations_connection_name);

                    if (\is_callable($migrating_closure)) {
                        $migrating_closure($migration_name, static::STATUS_MIGRATION_STARTED, $current, $total);
                    }

                    if ($this->executor->execute($migration_data, $migrations_connection_name)) {
                        $migrated[] = $migration_name;
                        $this->repository->insert($migration_name);

                        if (\is_callable($migrating_closure)) {
                            $migrating_closure($migration_name, static::STATUS_MIGRATION_COMPLETED, $current, $total);
                        }
                    }

                    $current++;
                }
            }
        }

        return $migrated;
    }

    /**
     * {@inheritdoc}
     */
    public function needToMigrateList(): array
    {
        $migrated_names     = $this->repository->migrations();
        $found_migrations   = $this->source->all();
        $not_migrated_names = \array_diff(Arr::flatten($found_migrations), $migrated_names);

        return \array_filter(\array_map(function (array $for_connection) use (&$not_migrated_names) {
            return \array_filter($for_connection, function ($migrations_name) use (&$not_migrated_names) {
                return \in_array($migrations_name, $not_migrated_names, true);
            });
        }, $found_migrations));
    }
}
