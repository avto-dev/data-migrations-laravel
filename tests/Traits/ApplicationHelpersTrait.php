<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Traits;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;

/**
 * Class ApplicationHelpersTrait.
 *
 * Трейт вспомогательных методов по работе с инстансом приложения и подобные.
 *
 * @mixin \Illuminate\Foundation\Testing\TestCase
 */
trait ApplicationHelpersTrait
{
    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return ApplicationContract
     */
    abstract public function createApplication();

    /**
     * Возвращает путь к файлу БД sqlite, используемой для тестов.
     *
     * @return string
     */
    abstract public function getDatabaseFilePath();

    /**
     * Возвращает контейнер консоли.
     *
     * @param ApplicationContract|null $app
     *
     * @return Kernel|\App\Console\Kernel
     */
    public function console(ApplicationContract $app = null)
    {
        $app = $this->resolveApplication($app);

        return $app->make(Kernel::class);
    }

    /**
     * Возвращает контейнер конфигов.
     *
     * @param ApplicationContract|null $app
     *
     * @return ConfigRepository
     */
    public function config(ApplicationContract $app = null)
    {
        $app = $this->resolveApplication($app);

        return $app->make('config');
    }

    /**
     * Устанавливает значение в окружение (или удаляет, если передать null).
     *
     * @param string $what
     * @param string $is
     *
     * @return bool
     */
    public function putenv($what, $is = null)
    {
        return putenv($is === null
            ? $what
            : sprintf('%s=%s', $what, $is));
    }

    /**
     * Устанавливает необходимое значение окружения приложения.
     *
     * @param string                   $environment
     * @param ApplicationContract|null $app
     *
     * @return void
     */
    public function setAppEnvironment($environment = 'testing', ApplicationContract $app = null)
    {
        $app = $this->resolveApplication($app);

        $this->putenv('APP_ENV', $environment);
        $this->config($app)->set('app.env', $environment);
        $app['env'] = $environment;
    }

    /**
     * Prepare the database.
     *
     * @param bool                     $recreate_db
     * @param ApplicationContract|null $app
     */
    public function prepareDatabase($recreate_db = true, ApplicationContract $app = null)
    {
        $app = $this->resolveApplication($app);

        /** @var \Illuminate\Filesystem\Filesystem $files */
        $files  = $app->make('files');
        $config = $this->config($app);

        $config->set('database.default', 'sqlite');
        $config->set('database.connections.sqlite.database', $database_file_path = $this->getDatabaseFilePath());

        if (! is_dir($database_directory_path = $files->dirname($database_file_path))) {
            $files->makeDirectory($database_directory_path, 0775, true);
        }

        if ($recreate_db === true) {
            if (file_exists($database_file_path)) {
                $this->assertTrue($files->delete($database_file_path));
            }

            $files->put($database_file_path, null);
        }

        $app->make('db')->reconnect();
    }

    /**
     * Clear cache.
     *
     * @param ApplicationContract|null $app
     *
     * @return void
     */
    public function clearCache(ApplicationContract $app = null)
    {
        $this->console($app)->call('cache:clear');
    }

    /**
     * Возвращает инстанс приложения.
     *
     * @param ApplicationContract|null $app
     *
     * @return ApplicationContract|\Illuminate\Foundation\Application
     */
    protected function resolveApplication(ApplicationContract $app = null)
    {
        if ($app instanceof ApplicationContract) {
            return $app;
        }

        if ($this->app instanceof ApplicationContract) {
            return $this->app;
        }

        return $this->createApplication();
    }
}
