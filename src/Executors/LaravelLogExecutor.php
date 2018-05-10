<?php

namespace AvtoDev\DataMigrationsLaravel\Executors;

use Illuminate\Support\Facades\Log;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;

/**
 * Class LaravelLogExecutor.
 *
 * Executor for writing data into laravel log.
 */
class LaravelLogExecutor implements ExecutorContract
{
    /**
     * {@inheritdoc}
     */
    public function execute($data, $connection_name = null)
    {
        $connection = \is_string($connection_name)
            ? $connection_name
            : 'default';

        Log::info('Data migration executed', [
            'connection' => $connection,
            'data'       => $data,
        ]);

        return true;
    }
}
