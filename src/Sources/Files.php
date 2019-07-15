<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel\Sources;

use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;

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
    public function __construct(Filesystem $files, string $migrations_path)
    {
        $this->files           = $files;
        $this->migrations_path = rtrim($migrations_path, '\\\/');
    }

    /**
     * Get the file system instance.
     *
     * @return Filesystem
     */
    public function filesystem(): Filesystem
    {
        return $this->files;
    }

    /**
     * {@inheritdoc}
     */
    public function migrations(string $connection_name = null): array
    {
        $path = $this->getPathForConnection($connection_name);

        if ($this->files->isDirectory($path)) {
            $migrations = \array_map(function ($file) {
                if ($file instanceof SplFileInfo) {
                    return $this->pathToName((string) $file->getRealPath());
                }

                return $this->pathToName((string) \realpath((string) $file));
            }, $this->files->files($path));

            \sort($migrations, SORT_NATURAL);

            return $migrations;
        }

        throw new InvalidArgumentException(sprintf('Directory [%s] does not exists', $path));
    }

    /**
     * {@inheritdoc}
     */
    public function connections(): array
    {
        $result = \array_map('basename', $this->files->directories($this->migrations_path));

        \sort($result, SORT_NATURAL);

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
    public function generateFileName($migration_name, Carbon $date = null, $extension = 'sql'): string
    {
        /** @var Carbon $when */
        $when = $date instanceof Carbon
            ? $date->copy()
            : Carbon::now();

        return \implode('_', [
                $when->format('Y_m_d'),
                \str_pad((string) $when->secondsSinceMidnight(), 6, '0', STR_PAD_LEFT),
                Str::slug($migration_name, '_'),
            ]) . '.' . ltrim($extension, '. ');
    }

    /**
     * {@inheritdoc}
     *
     * Returns created migration file path.
     */
    public function create(string $migration_name,
                           ?Carbon $date = null,
                           ?string $connection_name = null,
                           ?string $content = null): string
    {
        $file_name = $this->generateFileName($migration_name, $date);
        $file_path = $this->nameToPath($file_name, $connection_name);

        if (! $this->files->isDirectory($target_dir = \dirname($file_path))) {
            $this->files->makeDirectory($target_dir, 0755, true);
        }

        $this->files->put($file_path, (string) $content);

        return $file_path;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get(string $migration_name, ?string $connection_name = null): string
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
    public function all(): array
    {
        $migrations = [];

        foreach (\array_merge([null], $this->connections()) as $connection) {
            $migrations[$connection] = $this->migrations($connection);
        }

        return $migrations;
    }

    /**
     * Converts migration name into migration path.
     *
     * @param string      $name
     * @param string|null $connection_name
     *
     * @return string
     */
    public function nameToPath(string $name, string $connection_name = null): string
    {
        return $this->getPathForConnection($connection_name) . DIRECTORY_SEPARATOR . ltrim($name, '\\/');
    }

    /**
     * Converts migration path into migration name.
     *
     * @param string $path
     *
     * @return string
     */
    public function pathToName(string $path): string
    {
        return \basename($path);
    }

    /**
     * Read GZipped file and returns content as a string.
     *
     * @param string $file_path
     * @param int    $read_length
     *
     * @return string
     */
    protected function readGZippedFile(string $file_path, int $read_length = 4096): string
    {
        $result   = '';
        $resource = \gzopen($file_path, 'r');

        if (\is_resource($resource)) {
            while (! \gzeof($resource)) {
                $result .= \gzread($resource, $read_length);
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
    protected function getPathForConnection(string $connection_name = null): string
    {
        return $this->migrations_path . (\is_string($connection_name)
                ? DIRECTORY_SEPARATOR . $connection_name
                : '');
    }
}
