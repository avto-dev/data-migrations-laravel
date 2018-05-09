<?php

namespace AvtoDev\DataMigrationsLaravel;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class MigrationsFiles
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $migrations_path;

    /**
     * MigrationsFiles constructor.
     *
     * @param Filesystem $files
     * @param string     $migrations_path
     */
    public function __construct(Filesystem $files, $migrations_path)
    {
        $this->files           = $files;
        $this->migrations_path = $migrations_path;
    }

    /**
     * Get the file system instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get migrations files data.
     *
     * @param string|null $connection_name
     *
     * @return SplFileInfo[]
     */
    public function getMigrationsFiles($connection_name = null)
    {
        return $this->files->files($this->migrations_path . (\is_string($connection_name)
                ? DIRECTORY_SEPARATOR . $connection_name
                : ''));
    }

    /**
     * Get array of all available connections names.
     *
     * @return string[]
     */
    public function getConnectionsNames()
    {
        return array_map(function ($directory_path) {
            return basename($directory_path);
        }, $this->files->directories($this->migrations_path));
    }

    /**
     * Generate migration file name, based on migration name.
     *
     * @param string      $migration_name
     * @param Carbon|null $date
     * @param string      $extension
     *
     * @return string
     */
    public function generateFileName($migration_name, Carbon $date = null, $extension = 'sql')
    {
        /** @var Carbon $when */
        $when = $date instanceof Carbon
            ? $date->copy()
            : Carbon::now();

        return implode('_', [
                $when->format('Y_m_d'),
                \str_pad($when->secondsSinceMidnight(), 6, '0', STR_PAD_LEFT),
                Str::slug($migration_name, '_'),
            ]) . '.' . ltrim($extension, '.');
    }

    /**
     * Create migration file.
     *
     * @param string      $migration_name
     * @param Carbon|null $date
     * @param string|null $connection_name
     * @param string|null $content
     *
     * @return string
     */
    public function create($migration_name, Carbon $date = null, $connection_name = null, $content = null)
    {
        $file_name   = $this->generateFileName($migration_name, $date);
        $target_dir  = $this->migrations_path . (\is_string($connection_name)
                ? DIRECTORY_SEPARATOR . $connection_name
                : '');
        $target_path = $target_dir . DIRECTORY_SEPARATOR . $file_name;

        if (! $this->files->isDirectory($target_dir)) {
            $this->files->makeDirectory($target_dir, 0755, true);
        }

        $this->files->put($target_path, $content);

        return $target_path;
    }
}
