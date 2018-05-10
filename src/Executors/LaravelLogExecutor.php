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
        $message = ! is_null($connection_name)
            ? sprintf('Execute in connection \'%s\': %s', (string) $connection_name, (string) $data)
            : sprintf('Execute in default connection: %s', (string) $data);

        Log::info($message);
    }
}
