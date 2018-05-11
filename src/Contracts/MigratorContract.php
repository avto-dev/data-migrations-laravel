<?php

namespace AvtoDev\DataMigrationsLaravel\Contracts;

interface MigratorContract
{
    /**
     * Get the migrations repository instance.
     *
     * @return RepositoryContract
     */
    public function repository();

    /**
     * Get the migrations source instance.
     *
     * @return SourceContract
     */
    public function source();

    /**
     * Make migration.
     *
     * @param string|null $connection_name
     *
     * @return string[]
     */
    public function migrate($connection_name = null);

    /**
     * Get array of migrations, which is found in source but has no record in migrations repository (not migrated).
     * Array key is connection name, and value is array of migrations names.
     *
     * Important: migrations for default connection has kay name '' (empty string).
     *
     * @return array[]
     */
    public function notMigrated();
}
