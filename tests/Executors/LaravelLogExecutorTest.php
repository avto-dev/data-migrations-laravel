<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Executors;

use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Executors\LaravelLogExecutor;
use AvtoDev\DataMigrationsLaravel\Tests\AbstractTestCase;

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
        $data = "Test laravel log message";

        $this->executor->execute($data);

        $this->assertTrue(strpos(file_get_contents($this->getLoggingFilePath()), $data) !== false);
    }
}
