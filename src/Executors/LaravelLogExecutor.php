<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel\Executors;

/**
 * Executor for writing data into laravel log.
 */
class LaravelLogExecutor extends AbstractExecutor
{
    /**
     * {@inheritdoc}
     */
    public function execute($data, $connection_name = null)
    {
        $connection = \is_string($connection_name)
            ? $connection_name
            : 'default';

        $this->app->make(\Psr\Log\LoggerInterface::class)->info('Data migration executed', [
            'connection' => $connection,
            'data'       => $data,
        ]);

        return true;
    }
}
