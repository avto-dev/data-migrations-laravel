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
        $this->migrations_path = rtrim($migrations_path, '\\\/');
    }

    /**
     * Get the file system instance.
     *
     * @return Filesystem
     */
    public function filesystem()
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
            $migrations = array_map(function ($file) {
                if ($file instanceof SplFileInfo) {
                    return $this->pathToName($file->getRealPath());
                }

                return $this->pathToName(realpath((string) $file));
            }, $this->files->files($path));

            sort($migrations, SORT_NATURAL);

            return $migrations;
        }

        throw new InvalidArgumentException(sprintf('Directory [%s] does not exists', $path));
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
     *
     * Returns created migration file path.
     */
    public function create($migration_name, Carbon $date = null, $connection_name = null, $content = null)
    {
        $file_name = $this->generateFileName($migration_name, $date);
        $file_path = $this->nameToPath($file_name, $connection_name);

        if (! $this->files->isDirectory($target_dir = dirname($file_path))) {
            $this->files->makeDirectory($target_dir, 0755, true);
        }

        $this->files->put($file_path, $content);

        return $file_path;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get($migration_name, $connection_name = null)
    {
        $migration_path = $this->nameToPath($migration_name, $connection_name);

        switch (true) {
            case Str::endsWith($migration_path, '.gz'):
                return $this->readGZippedFile($migration_path);
            // Case '.zip', etc
        }

        return $this->files->get($migration_path);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $migrations = [];

        foreach (array_merge([null], $this->connections()) as $connection) {
            $migrations[$connection] = $this->migrations($connection);
        }

        return $migrations;
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
     * @param string|null $connection_name
     *
     * @return string
     */
    protected function getPathForConnection($connection_name = null)
    {
        return $this->migrations_path . (\is_string($connection_name)
                ? DIRECTORY_SEPARATOR . $connection_name
                : '');
    }

    /**
     * Converts migration name into migration path.
     *
     * @param string      $name
     * @param string|null $connection_name
     *
     * @return string
     */
    public function nameToPath($name, $connection_name = null)
    {
        return $this->getPathForConnection($connection_name) . DIRECTORY_SEPARATOR . ltrim($name, '\\\/');
    }

    /**
     * Converts migration path into migration name.
     *
     * @param string $path
     *
     * @return string
     */
    public function pathToName($path)
    {
        return basename($path);
    }
}
