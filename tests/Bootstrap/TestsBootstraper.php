<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Bootstrap;

/**
 * Class TestsBootstraper.
 */
class TestsBootstraper extends AbstractTestsBootstraper
{
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
}
