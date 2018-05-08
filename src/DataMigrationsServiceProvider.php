<?php

namespace AvtoDev\DataMigrationsLaravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

/**
 * Class DataMigrationsServiceProvider.
 */
class DataMigrationsServiceProvider extends IlluminateServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Get config root key name.
     *
     * @return string
     */
    public static function getConfigRootKeyName()
    {
        return basename(static::getConfigPath(), '.php');
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
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
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

        if ($this->app->runningInConsole()) {
            $this->registerArtisanCommands();
        }
    }

    /**
     * Register data migrations repository.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton(RepositoryContract::class, function (Application $app) {
            $config = $app->make('config')->get(static::getConfigRootKeyName());

            return new Repository($app->make('db')->connection($config['connection']), $config['table_name']);
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
