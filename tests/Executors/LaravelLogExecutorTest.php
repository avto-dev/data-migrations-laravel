<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Executors;

use Illuminate\Support\Str;
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
     * Log file path.
     *
     * @var string
     */
    protected $log_file_path = __DIR__ . '/../temp/storage/logs/laravel.log';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->executor = new LaravelLogExecutor;
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
     * Test execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $data = 'Test laravel log message ' . Str::random();

        $this->assertTrue($this->executor->execute($data));

        $this->assertFileExists($this->log_file_path);

        foreach ([$data, 'default', 'Data migration executed'] as $needle) {
            $this->assertContains($needle, file_get_contents($this->log_file_path));
        }
    }

    /**
     * Test execute with custom connection name.
     *
     * @return void
     */
    public function testExecuteWithCustomConnection()
    {
        $data = 'Custom statement ' . Str::random();

        $this->assertTrue($this->executor->execute($data, $connection = Str::random()));

        $this->assertFileExists($this->log_file_path);

        foreach ([$data, $connection, 'Data migration executed'] as $needle) {
            $this->assertContains($needle, file_get_contents($this->log_file_path));
        }
    }
}
