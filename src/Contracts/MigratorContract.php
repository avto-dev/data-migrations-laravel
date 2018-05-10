<?php

namespace AvtoDev\DataMigrationsLaravel\Contracts;

/**
 * Interface MigratorContract.
 */
interface MigratorContract
{
    /**
     * Run migrations.
     *
     * @param null|string $connection
     * @param bool $safe
     *
     * @return void
     */
    public function migrate($connection = null, $safe = false);

    /**
     * Return repository instance.
     *
     * @return RepositoryContract
     */
    public function repository();

    /**
     * Return source instance.
     *
     * @return SourceContract
     */
    public function source();
}
