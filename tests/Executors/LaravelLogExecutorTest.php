<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Executors;

use Illuminate\Support\Str;
use AvtoDev\DataMigrationsLaravel\Tests\AbstractTestCase;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Executors\LaravelLogExecutor;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\Executors\LaravelLogExecutor<extended>
 */
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
    public function setUp(): void
    {
        parent::setUp();

        $this->executor = new LaravelLogExecutor($this->app);
    }

    /**
     * Test instance implements contract.
     *
     * @return void
     */
    public function testInstance(): void
    {
        $this->assertInstanceOf(ExecutorContract::class, $this->executor);
    }

    /**
     * Test execute.
     *
     * @return void
     */
    public function testExecute(): void
    {
        $data = 'Test laravel log message ' . Str::random();

        $this->assertTrue($this->executor->execute($data));

        $this->assertFileExists($this->log_file_path);

        foreach ([$data, 'default', 'Data migration executed'] as $needle) {
            $this->assertStringContainsString($needle, file_get_contents($this->log_file_path));
        }
    }

    /**
     * Test execute with custom connection name.
     *
     * @return void
     */
    public function testExecuteWithCustomConnection(): void
    {
        $data = 'Custom statement ' . Str::random();

        $this->assertTrue($this->executor->execute($data, $connection = Str::random()));

        $this->assertFileExists($this->log_file_path);

        foreach ([$data, $connection, 'Data migration executed'] as $needle) {
            $this->assertStringContainsString($needle, file_get_contents($this->log_file_path));
        }
    }
}
