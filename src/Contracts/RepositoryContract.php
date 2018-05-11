<?php

namespace AvtoDev\DataMigrationsLaravel\Contracts;

use Illuminate\Database\Connection;

interface RepositoryContract
{
    /**
     * Resolve the database connection instance.
     *
     * @return Connection
     */
    public function getConnection();

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists();

    /**
     * Create the migration repository (storage).
     *
     * @return void
     */
    public function createRepository();

    /**
     * Delete the migration repository (storage).
     *
     * @return void
     */
    public function deleteRepository();

    /**
     * Remove a migration record from the repository storage.
     *
     * @param string $name
     *
     * @return void
     */
    public function delete($name);

    /**
     * Insert migration record into table.
     *
     * @param string $name
     *
     * @return void
     */
    public function insert($name);

    /**
     * Get list of migrations.
     *
     * @return string[]
     */
    public function migrations();

    /**
     * Remove all migrations records.
     *
     * @return bool
     */
    public function clear();
}
