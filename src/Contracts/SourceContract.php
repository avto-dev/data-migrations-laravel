<?php

namespace AvtoDev\DataMigrationsLaravel\Contracts;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Interface SourceContract.
 */
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
    public function migrations($connection_name = null);

    /**
     * Get array of all available connections names.
     *
     * @return string[]
     */
    public function connections();

    /**
     * Create migration.
     *
     * @param string      $migration_name
     * @param Carbon|null $date
     * @param string|null $connection_name
     * @param string|null $content
     *
     * @return string
     */
    public function create($migration_name, Carbon $date = null, $connection_name = null, $content = null);

    /**
     * Get migration content.
     *
     * @param string $path
     *
     * @return string
     */
    public function getContent($path);
}
