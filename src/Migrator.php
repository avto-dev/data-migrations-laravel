<?php

namespace AvtoDev\DataMigrationsLaravel;

use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class Migrator
{
    /**
     * @var RepositoryContract
     */
    protected $repository;

    /**
     * @var string
     */
    protected $migrations_path;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Migrator constructor.
     *
     * @param RepositoryContract $repository
     * @param Filesystem         $files
     * @param string             $migrations_path
     */
    public function __construct(RepositoryContract $repository, Filesystem $files, $migrations_path)
    {
        $this->repository      = $repository;
        $this->files           = $files;
        $this->migrations_path = $migrations_path;
    }

    /**
     * Get migrations repository instance.
     *
     * @return RepositoryContract
     */
    public function getRepository()
    {
        return $this->repository;
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


}
