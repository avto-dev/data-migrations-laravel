<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use InvalidArgumentException;
use AvtoDev\DataMigrationsLaravel\Migrator;
use AvtoDev\DataMigrationsLaravel\Repository;
use AvtoDev\DataMigrationsLaravel\Sources\Files;
use AvtoDev\DataMigrationsLaravel\ServiceProvider;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use AvtoDev\DataMigrationsLaravel\Executors\DatabaseRawQueryExecutor;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\ServiceProvider<extended>
 *
 * @group provider
 */
class ServiceProviderTest extends AbstractTestCase
{
    /**
     * @var string
     */
    protected $config_root_key;

    /**
     * @var ServiceProvider
     */
    protected $provider_instance;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->config_root_key = ServiceProvider::getConfigRootKeyName();

        $this->provider_instance = new ServiceProvider($this->app);
    }

    /**
     * Проверка существования и корректности значений конфигурации.
     *
     * @return void
     */
    public function testConfigExists(): void
    {
        $config = $this->app->make('config')->get($this->config_root_key);

        $this->assertIsArray($config);

        foreach (['table_name', 'connection', 'migrations_path', 'executor_class'] as $key) {
            $this->assertArrayHasKey($key, $config);
        }

        foreach (['table_name', 'migrations_path', 'executor_class'] as $key) {
            $this->assertNotEmpty($config[$key]);
        }
    }

    /**
     * Tests service-provider loading.
     *
     * @return void
     */
    public function testServiceProviderLoading(): void
    {
        $this->assertInstanceOf(Repository::class, $this->app[RepositoryContract::class]);
        $this->assertInstanceOf(Repository::class, app(RepositoryContract::class));

        $this->assertInstanceOf(Migrator::class, $this->app[MigratorContract::class]);
        $this->assertInstanceOf(Migrator::class, app(MigratorContract::class));

        $this->assertInstanceOf(Files::class, $this->app[SourceContract::class]);
        $this->assertInstanceOf(Files::class, app(SourceContract::class));

        $this->assertInstanceOf(DatabaseRawQueryExecutor::class, $this->app[ExecutorContract::class]);
        $this->assertInstanceOf(DatabaseRawQueryExecutor::class, app(ExecutorContract::class));
    }

    /**
     * Test throws exception on wrong executor class name in configuration.
     *
     * @return void
     */
    public function testExceptionOnInvalidExecutorClassName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('~Invalid executor class.*~i');

        $this->putenv('DATA_MIGRATIONS_EXECUTOR_CLASS', \stdClass::class);

        $this->refreshApplication();

        $this->app->make(ExecutorContract::class);
    }
}
