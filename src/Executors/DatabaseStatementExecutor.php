<?php

namespace AvtoDev\DataMigrationsLaravel\Executors;

use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use Illuminate\Support\Facades\DB;

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
        if (! is_string($data) || empty($data)) {
            return;
        }

        DB::connection($connection_name)->statement($data);
    }
}
