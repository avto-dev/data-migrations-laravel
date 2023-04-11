<?php

namespace AvtoDev\DataMigrationsLaravel\Contracts;

use Carbon\Carbon;
use InvalidArgumentException;

interface SourceContract
{
    /**
     * Get the migrations names list.
     *
     * @param string|null $connection_name
     *
     * @throws InvalidArgumentException
     *
     * @return string[]
     */
    public function migrations(?string $connection_name = null): array;

    /**
     * Get array of all available connections names.
     *
     * @return string[]
     */
    public function connections(): array;

    /**
     * Create migration.
     *
     * @param string      $migration_name
     * @param Carbon|null $date
     * @param string|null $connection_name
     * @param string|null $content
     *
     * @return mixed|void
     */
    public function create(string $migration_name,
                           ?Carbon $date = null,
                           ?string $connection_name = null,
                           ?string $content = null);

    /**
     * Get migration data by migration name.
     *
     * @param string      $migration_name
     * @param string|null $connection_name
     *
     * @return mixed
     */
    public function get(string $migration_name, ?string $connection_name = null);

    /**
     * Get all migrations as an array, where array key is connection name, and value is array of migrations names.
     *
     * Important: migrations for default connection has kay name '' (empty string).
     *
     * @return string[][]
     */
    public function all(): array;
}
