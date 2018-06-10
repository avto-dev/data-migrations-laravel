<?php

declare(strict_types = 1);

namespace AvtoDev\DataMigrationsLaravel;

use InvalidArgumentException;
use AvtoDev\DataMigrationsLaravel\Sources\Files;
use Illuminate\Contracts\Foundation\Application;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

/**
 * Class DataMigrationsServiceProvider.
 */
class DataMigrationsServiceProvider extends IlluminateServiceProvider
{
    /**
     * Get config root key name.
     *
     * @return string
     */
    public static function getConfigRootKeyName()
    {
        return \basename(static::getConfigPath(), '.php');
    }

    /**
     * Returns path to the configuration file.
     *
     * @return string
     */
    public static function getConfigPath()
    {
        return __DIR__ . '/config/data-migrations.php';
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->initializeConfigs();

        $this->registerRepository();
        $this->registerSource();
        $this->registerExecutor();
        $this->registerMigrator();

        if ($this->app->runningInConsole()) {
            $this->registerArtisanCommands();
        }
    }

    /**
     * Returns package configuration as an array.
     *
     * @return array
     */
    protected function getPackageConfiguration()
    {
        return $this->app->make('config')->get(static::getConfigRootKeyName());
    }

    /**
     * Register data migrations repository.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton(RepositoryContract::class, function (Application $app) {
            $config = $this->getPackageConfiguration();

            return new Repository($app->make('db')->connection($config['connection']), $config['table_name']);
        });
    }

    /**
     * Register data source instance.
     *
     * @return void
     */
    protected function registerSource()
    {
        $this->app->bind(SourceContract::class, function (Application $app) {
            $config = $this->getPackageConfiguration();

            return new Files($app->make('files'), $config['migrations_path']);
        });
    }

    /**
     * Register migrations executor instance.
     *
     * @return void
     */
    protected function registerExecutor()
    {
        $this->app->bind(ExecutorContract::class, function (Application $app) {
            $config   = $this->getPackageConfiguration();
            $executor = new $config['executor_class']($app);

            if ($executor instanceof ExecutorContract) {
                return $executor;
            }

            throw new InvalidArgumentException(sprintf(
                'Invalid executor class (must implements interface %s)', ExecutorContract::class
            ));
        });
    }

    /**
     * Register data migrator instance.
     *
     * @return void
     */
    protected function registerMigrator()
    {
        $this->app->singleton(MigratorContract::class, function (Application $app) {
            return new Migrator(
                $app->make(RepositoryContract::class),
                $app->make(SourceContract::class),
                $app->make(ExecutorContract::class)
            );
        });
    }

    /**
     * Register artisan-commands.
     *
     * @return void
     */
    protected function registerArtisanCommands()
    {
        $this->commands([
            Commands\InstallCommand::class,
            Commands\MigrateCommand::class,
            Commands\MakeCommand::class,
            Commands\StatusCommand::class,
            Commands\UninstallCommand::class,
        ]);
    }

    /**
     * Initialize configs.
     *
     * @return void
     */
    protected function initializeConfigs()
    {
        $this->mergeConfigFrom(static::getConfigPath(), static::getConfigRootKeyName());

        $this->publishes([
            realpath(static::getConfigPath()) => config_path(basename(static::getConfigPath())),
        ], 'config');
    }
}
