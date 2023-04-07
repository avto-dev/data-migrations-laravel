<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel\Executors;

use Illuminate\Database\ConnectionResolverInterface;

/**
 * Executor that writing data into database.
 */
class DatabaseRawQueryExecutor extends AbstractExecutor
{
    /**
     * {@inheritdoc}
     */
    public function execute($data, ?string $connection_name = null): bool
    {
        if (\is_string($data) && $data !== '') {
            /** @var ConnectionResolverInterface $db */
            $db = $this->app->make('db');

            return $db->connection($connection_name)->unprepared($data);
        }

        return false;
    }
}
