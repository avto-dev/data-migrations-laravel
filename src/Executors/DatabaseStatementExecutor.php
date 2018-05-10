<?php

namespace AvtoDev\DataMigrationsLaravel\Executors;

use Illuminate\Support\Facades\DB;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;

/**
 * Class DatabaseStatementExecutor.
 *
 * Executor that writing data into database.
 */
class DatabaseStatementExecutor implements ExecutorContract
{
    /**
     * {@inheritdoc}
     */
    public function execute($data, $connection_name = null)
    {
        if (! \is_string($data) || empty($data)) {
            return false;
        }

        return DB::connection($connection_name)->statement($data);
    }
}
