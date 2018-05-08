<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use InvalidArgumentException;
use Illuminate\Database\Connection;
use AvtoDev\DataMigrationsLaravel\Repository;

class RepositoryTest extends AbstractTestCase
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $table_name = 'migrations_data_test';

    /**
     * Create data migrations table into database?
     *
     * @var bool
     */
    protected $create_repository = false;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = new Repository(
            $this->app->make('db')->connection(),
            $this->table_name
        );
    }

    /**
     * Test migrations table creation.
     *
     * @return void
     */
    public function testRepositoryTableCreation()
    {
        $this->assertFalse($this->repository->repositoryExists());
        $this->assertTableNotExists($this->table_name);

        $this->repository->createRepository();

        $this->assertTrue($this->repository->repositoryExists());
        $this->assertTableExists($this->table_name);
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf(Connection::class, $this->repository->getConnection());
    }

    /**
     * Test for a got exception with invalid connection name.
     *
     * @return void
     */
    public function testGetWrongConnectionException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->repository = new Repository(
            $this->app->make('db')->connection('foo bar'),
            $this->table_name
        );

        $this->repository->getConnection();
    }

    public function testDelete()
    {
        $this->repository->createRepository();

        foreach (['foo', 'bar', $delete = 'bla bla'] as $migration_name) {
            $this->repository->insert($migration_name);
        }

        $this->repository->delete($delete);

        $this->assertNotContains($delete, $this->repository->getMigrations());
    }

    /**
     * Test migrations records inserting.
     *
     * @return void
     */
    public function testInsert()
    {
        $this->repository->createRepository();

        $migrations = ['foo', 'bar', 'udaff', 'bla bla'];

        foreach ($migrations as $migration_name) {
            $this->repository->insert($migration_name);
        }

        $inserted_migrations = $this->repository->getMigrations();

        $this->assertCount(count($migrations), $inserted_migrations);

        foreach ($migrations as $migration_name) {
            $this->assertContains($migration_name, $inserted_migrations);
        }
    }
}
