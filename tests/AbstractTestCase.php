<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider;
use AvtoDev\DataMigrationsLaravel\Tests\Bootstrap\TestsBootstrapper;
use AvtoDev\DevTools\Tests\PHPUnit\AbstractLaravelTestCase;
use Illuminate\Foundation\Application;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends AbstractLaravelTestCase
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
    protected function beforeApplicationBootstrapped(Application $app)
    {
        $app->useStoragePath(TestsBootstrapper::getStorageDirectoryPath());
    }

    /**
     * {@inheritdoc}
     */
    protected function afterApplicationBootstrapped(Application $app)
    {
        $app->register(DataMigrationsServiceProvider::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
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
     * @return bool|string
     */
    public function getTemporaryDirectoryPath()
    {
        return realpath(__DIR__ . '/temp');
    }

    /**
     * Returns connections names and paths for SQLite databases.
     *
     * @return string[]
     */
    public function getDatabasesFilePath()
    {
        return [
            'default'      => static::getTemporaryDirectoryPath() . '/database.sqlite',
            'connection_2' => static::getTemporaryDirectoryPath() . '/database_2.sqlite',
            'connection_3' => static::getTemporaryDirectoryPath() . '/database_3.sqlite',
        ];
    }
}
