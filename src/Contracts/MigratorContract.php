<?php

namespace AvtoDev\DataMigrationsLaravel\Contracts;

use Closure;

interface MigratorContract
{
    /**
     * Get the migrations repository instance.
     *
     * @return RepositoryContract
     */
    public function repository(): RepositoryContract;

    /**
     * Get the migrations source instance.
     *
     * @return SourceContract
     */
    public function source(): SourceContract;

    /**
     * Make migration.
     *
     * Optionally you can pass closure for making migration process more interactive. For closure will be passed next
     * parameters:
     *
     *   - $migration_name
     *   - $status
     *   - $current
     *   - $total
     *
     * @param string|null  $connection_name
     * @param Closure|null $migrating_closure
     *
     * @return string[]
     */
    public function migrate(string $connection_name = null, Closure $migrating_closure = null): array;

    /**
     * Get array of migrations, which is found in source but has no record in migrations repository (not migrated).
     * Array key is connection name, and value is array of migrations names.
     *
     * Important: migrations for default connection has kay name '' (empty string).
     *
     * @return array[]
     */
    public function needToMigrateList(): array;
}
