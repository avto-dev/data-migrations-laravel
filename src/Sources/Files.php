<?php

namespace AvtoDev\DataMigrationsLaravel\Sources;

use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Finder\SplFileInfo;

class Files implements SourceContract
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
     * Files constructor.
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
     * {@inheritdoc}
     */
    public function migrations($connection_name = null)
    {
        $path = $this->getPathForConnection($connection_name);

        if ($this->files->isDirectory($path)) {
            $result = array_map(function ($file) {
                if ($file instanceof SplFileInfo) {
                    return $file->getRealPath();
                }

                return realpath((string) $file);
            }, $this->files->files($path));

            sort($result, SORT_NATURAL);

            return $result;
        }

        throw new InvalidArgumentException(sprintf(
            'Directory [%s] for %s does not exists',
            $path,
            \is_string($connection_name)
                ? 'connection "' . $connection_name . '"'
                : 'default connection'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function connections()
    {
        $result = array_map(function ($directory_path) {
            return basename($directory_path);
        }, $this->files->directories($this->migrations_path));

        sort($result, SORT_NATURAL);

        return $result;
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
            ]) . '.' . ltrim($extension, '. ');
    }

    /**
     * {@inheritdoc}
     */
    public function create($migration_name, Carbon $date = null, $connection_name = null, $content = null)
    {
        $file_name   = $this->generateFileName($migration_name, $date);
        $target_dir  = $this->getPathForConnection($connection_name);
        $target_path = $target_dir . DIRECTORY_SEPARATOR . $file_name;

        if (! $this->files->isDirectory($target_dir)) {
            $this->files->makeDirectory($target_dir, 0755, true);
        }

        $this->files->put($target_path, $content);

        return $target_path;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent($path)
    {
        switch (true) {
            case Str::endsWith($path, '.gz'):
                return $this->readGZippedFile($path);
            // Case '.zip', etc
        }

        return $this->files->get($path);
    }

    /**
     * Read GZipped file and returns content as a string.
     *
     * @param string $file_path
     * @param int    $read_length
     *
     * @return string
     */
    protected function readGZippedFile($file_path, $read_length = 4096)
    {
        $result   = '';
        $resource = gzopen($file_path, 'r');

        if (is_resource($resource)) {
            while (! gzeof($resource)) {
                $result .= gzread($resource, $read_length);
            }
        }

        return $result;
    }

    /**
     * Returns path for directory with migrations (using connection name).
     *
     * @param string null $connection_name
     *
     * @return string
     */
    protected function getPathForConnection($connection_name = null)
    {
        return $this->migrations_path . (\is_string($connection_name)
                ? DIRECTORY_SEPARATOR . $connection_name
                : '');
    }
}
