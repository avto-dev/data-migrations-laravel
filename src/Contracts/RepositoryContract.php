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
    public function getConnection(): Connection;

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists(): bool;

    /**
     * Create the migration repository (storage).
     *
     * @return void
     */
    public function createRepository(): void;

    /**
     * Delete the migration repository (storage).
     *
     * @return void
     */
    public function deleteRepository(): void;

    /**
     * Remove a migration record from the repository storage.
     *
     * @param string $name
     *
     * @return void
     */
    public function delete(string $name): void;

    /**
     * Insert migration record into table.
     *
     * @param string $name
     *
     * @return void
     */
    public function insert(string $name): void;

    /**
     * Get list of migrations.
     *
     * @return string[]
     */
    public function migrations(): array;

    /**
     * Remove all migrations records.
     *
     * @return bool
     */
    public function clear(): bool;
}
