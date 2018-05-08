<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider;
use AvtoDev\DataMigrationsLaravel\Repository;

/**
 * Class DataMigrationsServiceProviderTest.
 *
 * @group provider
 */
class DataMigrationsServiceProviderTest extends AbstractTestCase
{
    /**
     * @var string
     */
    protected $config_root_key;

    /**
     * @var DataMigrationsServiceProvider
     */
    protected $provider_instance;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->config_root_key = DataMigrationsServiceProvider::getConfigRootKeyName();

        $this->provider_instance = new DataMigrationsServiceProvider($this->app);
    }

    /**
     * Проверка существования и корректности значений конфигурации.
     *
     * @return void
     */
    public function testConfigExists()
    {
        $config = config($this->config_root_key);

        $this->assertIsArray($config);

        foreach (['table_name'] as $key) {
            $this->assertArrayHasKey($key, $config);
        }
    }

    /**
     * Tests service-provider loading.
     *
     * @return void
     */
    public function testServiceProviderLoading()
    {
        $this->assertInstanceOf(Repository::class, $this->app[RepositoryContract::class]);
        $this->assertInstanceOf(Repository::class, app(RepositoryContract::class));
    }
}
