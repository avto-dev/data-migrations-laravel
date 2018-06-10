<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Bootstrap;

use AvtoDev\DevTools\Tests\Bootstrap\AbstractLaravelTestsBootstrapper;
use AvtoDev\DevTools\Tests\PHPUnit\Traits\CreatesApplicationTrait;

class TestsBootstrapper extends AbstractLaravelTestsBootstrapper
{
    use CreatesApplicationTrait;

    /**
     * Returns `storage` directory path.
     *
     * @return string
     */
    public static function getStorageDirectoryPath(): string
    {
        return __DIR__ . '/../temp/storage';
    }

    /**
     * Prepare `storage` directory for test execution.
     *
     * @return bool
     */
    protected function bootPrepareStorageDirectory(): bool
    {
        $this->log('Prepare storage directory');

        if ($this->files->isDirectory($storage = static::getStorageDirectoryPath())) {
            if ($this->files->deleteDirectory($storage)) {
                $this->log('Previous storage directory deleted successfully');
            } else {
                $this->log("Cannot delete directory [{$storage}]");

                return false;
            }
        }

        $this->files->copyDirectory(__DIR__ . '/../../vendor/laravel/laravel/storage', $storage);

        return true;
    }
}
