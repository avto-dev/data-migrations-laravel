<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider;

abstract class AbstractTestCase extends TestCase
{
    use Traits\ApplicationHelpersTrait;

    /**
     * Create data migrations table into database?
     *
     * @var bool
     */
    protected $create_repository = true;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->config()->set(
            DataMigrationsServiceProvider::getConfigRootKeyName() . '.migrations_path',
            __DIR__ . '/stubs/data_migrations'
        );

        $this->prepareDatabase(true);

        if ($this->create_repository === true) {
            $this->app->make(RepositoryContract::class)->createRepository();
        }
    }

    /**
     * Возвращает путь к директории, в которой хранятся временные файлы.
     *
     * @return string
     */
    public function getTemporaryDirectoryPath(): string
    {
        return (string) \realpath(__DIR__ . '/temp');
    }

    /**
     * Returns connections names and paths for SQLite databases.
     *
     * @return string[]
     */
    public function getDatabasesFilePath(): array
    {
        return [
            'default'      => $this->getTemporaryDirectoryPath() . '/database.sqlite',
            'connection_2' => $this->getTemporaryDirectoryPath() . '/database_2.sqlite',
            'connection_3' => $this->getTemporaryDirectoryPath() . '/database_3.sqlite',
        ];
    }

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $app->useStoragePath(__DIR__ . '/temp/storage');

        $app->make(Kernel::class)->bootstrap();

        // Register our service-provider manually
        $app->register(DataMigrationsServiceProvider::class);

        return $app;
    }
}
