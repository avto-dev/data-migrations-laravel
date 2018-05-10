<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Executors;

use AvtoDev\DataMigrationsLaravel\Tests\AbstractTestCase;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Executors\LaravelLogExecutor;

class LaravelLogExecutorTest extends AbstractTestCase
{
    /**
     * @var LaravelLogExecutor
     */
    protected $executor;

    /**
     * Create data migrations table into database?
     *
     * @var bool
     */
    protected $create_repository = false;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->executor = new LaravelLogExecutor();
    }

    /**
     * Test instance implements contract.
     *
     * @return void
     */
    public function testInstance()
    {
        $this->assertInstanceOf(ExecutorContract::class, $this->executor);
    }

    /**
     * Test execute data.
     *
     * @return void
     */
    public function testExecute()
    {
        $data          = 'Test laravel log message';
        $log_file_path = __DIR__ . '/../temp/storage/logs/laravel.log';

        $this->assertTrue($this->executor->execute($data));

        $this->assertFileExists($log_file_path);

        foreach ([$data, 'default', 'Data migration executed'] as $needle) {
            $this->assertContains($needle, file_get_contents($log_file_path));
        }
    }
}
