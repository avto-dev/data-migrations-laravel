<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use AvtoDev\DataMigrationsLaravel\Contracts\DataMigrationsRepositoryContract;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends BaseTestCase
{
    use Traits\CreatesApplicationTrait,
        Traits\AdditionalAssertsTrait,
        Traits\ApplicationHelpersTrait;

    /**
     * Create data migrations table into database?
     *
     * @var bool
     */
    protected $create_repository = true;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->prepareDatabase(true);

        if ($this->create_repository === true) {
            $this->app->make(DataMigrationsRepositoryContract::class)->createRepository();
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
     * Возвращает путь к файлу БД sqlite, используемой для тестов.
     *
     * @return string
     */
    public function getDatabaseFilePath()
    {
        return static::getTemporaryDirectoryPath() . '/database.sqlite';
    }
}
