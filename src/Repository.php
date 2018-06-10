<?php

declare(strict_types = 1);

namespace AvtoDev\DataMigrationsLaravel;

use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Builder as QueryBuilder;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class Repository implements RepositoryContract
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table_name;

    /**
     * Repository constructor.
     *
     * @param Connection $connection
     * @param string     $table_name
     */
    public function __construct(Connection $connection, $table_name)
    {
        $this->connection = $connection;
        $this->table_name = $table_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function repositoryExists()
    {
        return $this->getConnection()->getSchemaBuilder()->hasTable($this->table_name);
    }

    /**
     * {@inheritdoc}
     */
    public function createRepository()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->table_name, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('migration')->unique();
            $table->dateTime('migrated_at');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRepository()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->drop($this->table_name);
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
    public function migrations()
    {
        return $this->table()
            ->orderBy('id', 'desc')
            ->pluck('migration')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->table()->delete() > 0;
    }

    /**
     * Get a query builder for the migration table.
     *
     * @return QueryBuilder
     */
    protected function table()
    {
        return $this->getConnection()->table($this->table_name)->useWritePdo();
    }
}
