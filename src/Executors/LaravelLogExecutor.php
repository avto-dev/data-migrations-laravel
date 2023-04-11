<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel\Executors;

use Psr\Log\LoggerInterface;

/**
 * Executor for writing data into laravel log.
 */
class LaravelLogExecutor extends AbstractExecutor
{
    /**
     * {@inheritdoc}
     */
    public function execute($data, ?string $connection_name = null): bool
    {
        $connection = \is_string($connection_name)
            ? $connection_name
            : 'default';

        /** @var LoggerInterface $logger */
        $logger = $this->app->make(LoggerInterface::class);
        $logger->info('Data migration executed', [
            'connection' => $connection,
            'data'       => $data,
        ]);

        return true;
    }
}
