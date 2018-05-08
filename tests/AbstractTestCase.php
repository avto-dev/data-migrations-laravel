<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends BaseTestCase
{
    use Traits\CreatesApplicationTrait,
        Traits\AdditionalAssertsTrait,
        Traits\ApplicationHelpersTrait;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->prepareDatabase();

        $this->app->register(DataMigrationsServiceProvider::class);

        $this->migrateDatabase();
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
     * Возвращает путь к файлу БД sqlite, используемой для тестов.
     *
     * @return string
     */
    public function getDatabaseFilePath()
    {
        return static::getTemporaryDirectoryPath() . '/database.sqlite';
    }

    /**
     * Мигрирует БД.
     *
     * @param Application|null $app
     *
     * @return void
     */
    public function migrateDatabase(Application $app = null)
    {
        $console = $this->console($app);

        $console->call('migrate:install');
        $console->call('migrate', [
            '--path' => '../../../tests/temp/migrations', // Путь должен быть строго относительный :(
        ]);
    }
}
