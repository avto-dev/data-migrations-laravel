<?php

namespace AvtoDev\DataMigrationsLaravel;

use AvtoDev\DataMigrationsLaravel\Contracts\DataMigrationsRepositoryContract;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;

class DataMigrationsRepository implements DataMigrationsRepositoryContract
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $config = [
        'table_name'      => null,
        'connection'      => null,
        'migrations_path' => null,
    ];

    /**
     * DataMigrationsRepository constructor.
     *
     * @param Application $app
     * @param array       $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app    = $app;
        $this->config = array_replace_recursive($this->config, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->app->make('db')->connection($this->config['connection']);
    }

    /**
     * Get a query builder for the migration table.
     *
     * @return QueryBuilder
     */
    protected function table()
    {
        return $this->getConnection()->table($this->config['table_name'])->useWritePdo();
    }

    /**
     * {@inheritdoc}
     */
    public function repositoryExists()
    {
        return $this->getConnection()->getSchemaBuilder()->hasTable($this->config['table_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function createRepository()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->config['table_name'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('migration')->unique();
            $table->dateTime('migrated_at');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete($name)
    {
        $this->table()->where('migration', $name)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function insert($name)
    {
        $record = ['migration' => $name, 'migrated_at' => Carbon::now()];

        $this->table()->insert($record);
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrations()
    {
        return $this->table()
            ->orderBy('id', 'desc')
            ->pluck('migration')->all();
    }
}
