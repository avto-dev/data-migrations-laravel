<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel\Executors;

use Illuminate\Database\ConnectionInterface;

/**
 * Executor that writing data into database.
 */
class DatabaseRawQueryExecutor extends AbstractExecutor
{
    /**
     * {@inheritdoc}
     */
    public function execute($data, string $connection_name = null): bool
    {
        if (\is_string($data) && ! empty($data)) {
            /** @var ConnectionInterface $connection */
            $connection = $this->app->make('db')->connection($connection_name);

            return $connection->unprepared($data);
        }

        return false;
    }
}
