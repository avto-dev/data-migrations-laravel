<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Bootstrap;

use AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider;
use Illuminate\Contracts\Console\Kernel;

/**
 * Class TestsBootstraper.
 */
class TestsBootstraper extends AbstractTestsBootstraper
{
    /**
     * Возвращает путь к директории с миграциями.
     *
     * @return string
     */
    public static function getMigrationsDirectoryPath()
    {
        return __DIR__ . '/../temp/migrations';
    }

    /**
     * Возвращает путь к директории `storage`.
     *
     * @return string
     */
    public static function getStorageDirectoryPath()
    {
        return __DIR__ . '/../temp/storage';
    }

    /**
     * Подготавливает директорию `storage` для выполнения тестов.
     *
     * @return bool
     */
    protected function bootPrepareStorageDirectory()
    {
        $this->log('Prepare storage directory');

        if ($this->files->isDirectory($storage = static::getStorageDirectoryPath())) {
            if ($this->files->deleteDirectory($storage)) {
                $this->log('Previous storage directory deleted successfully');
            } else {
                $this->log(sprintf('Cannot delete directory "%s"', $storage));
                return false;
            }
        }

        $this->files->copyDirectory(__DIR__ . '/../../vendor/laravel/laravel/storage', $storage);

        return true;
    }

    /**
     * Регистрирует необходимые сервис-провайдеры приложения.
     *
     * @return bool
     */
    protected function bootServiceProviders()
    {
        $this->log('Register service-providers');

        $this->app->register(DataMigrationsServiceProvider::class);

        return true;
    }

    /**
     * Удаляет предыдущие миграции (если они имеют место быть).
     *
     * @return bool
     */
    protected function bootCleanupPreviousMigrations()
    {
        $this->log('Cleanup previous migrations');

        foreach (glob(static::getMigrationsDirectoryPath() . DIRECTORY_SEPARATOR . '*.php') as $file_path) {
            if (! $this->files->delete($file_path)) {
                $this->log(sprintf('Cannot delete file "%s"', $file_path), 'error');
                return false;
            } else {
                $this->log(sprintf('Migration file "%s" deleted successfully', basename($file_path)));
            }
        }

        return true;
    }

    /**
     * Создаёт необходимые для тестирования файлы миграций.
     *
     * @return bool
     */
    protected function bootMakeMigrations()
    {
        $this->log('Make migrations files (using package command)');

        $this->app->make(Kernel::class)->call('data-migrations:table', [
            '--path' => static::getMigrationsDirectoryPath(),
        ]);

        return true;
    }
}
