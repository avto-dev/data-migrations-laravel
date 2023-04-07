<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel;

use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Container\Container;
use AvtoDev\DataMigrationsLaravel\Sources\Files;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Get config root key name.
     *
     * @return string
     */
    public static function getConfigRootKeyName(): string
    {
        return \basename(static::getConfigPath(), '.php');
    }

    /**
     * Returns path to the configuration file.
     *
     * @return string
     */
    public static function getConfigPath(): string
    {
        return __DIR__ . '/../config/data-migrations.php';
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
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
     * @return array<string, mixed>
     */
    protected function getPackageConfiguration(): array
    {
        /** @var ConfigRepository $repository */
        $repository = $this->app->make('config');

        /** @var array<string, mixed> $config */
        $config = $repository->get(static::getConfigRootKeyName());

        return $config;
    }

    /**
     * Register data migrations repository.
     *
     * @return void
     */
    protected function registerRepository(): void
    {
        $this->app->singleton(RepositoryContract::class, function (Container $app): RepositoryContract {
            /** @var array{connection: string|null, table_name: string} $config */
            $config = $this->getPackageConfiguration();

            /** @var DatabaseManager $db */
            $db = $app->make('db');

            return new Repository($db->connection($config['connection']), $config['table_name']);
        });
    }

    /**
     * Register data source instance.
     *
     * @return void
     */
    protected function registerSource(): void
    {
        $this->app->bind(SourceContract::class, function (Container $app): SourceContract {
            /** @var Filesystem $files */
            $files = $app->make('files');

            /** @var array{migrations_path: string} $config */
            $config = $this->getPackageConfiguration();

            return new Files($files, $config['migrations_path']);
        });
    }

    /**
     * Register migrations executor instance.
     *
     * @return void
     */
    protected function registerExecutor(): void
    {
        $this->app->bind(ExecutorContract::class, function (Container $app): ExecutorContract {
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
    protected function registerMigrator(): void
    {
        $this->app->singleton(MigratorContract::class, function (Container $app): MigratorContract {
            /** @var RepositoryContract $repository */
            $repository = $app->make(RepositoryContract::class);

            /** @var SourceContract $source */
            $source = $app->make(SourceContract::class);

            /** @var ExecutorContract $executor */
            $executor = $app->make(ExecutorContract::class);

            return new Migrator($repository, $source, $executor);
        });
    }

    /**
     * Register artisan-commands.
     *
     * @return void
     */
    protected function registerArtisanCommands(): void
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
    protected function initializeConfigs(): void
    {
        $this->mergeConfigFrom(static::getConfigPath(), static::getConfigRootKeyName());

        $this->publishes([
            \realpath(static::getConfigPath()) => config_path(\basename(static::getConfigPath())),
        ], 'config');
    }
}
