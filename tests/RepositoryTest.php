<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use InvalidArgumentException;
use Illuminate\Database\Connection;
use PHPUnit\Framework\AssertionFailedError;
use AvtoDev\DataMigrationsLaravel\Repository;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\Repository
 */
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
    public function setUp(): void
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
    public function testRepositoryTableCreation(): void
    {
        $this->assertFalse($this->repository->repositoryExists());
        $this->assertTableNotExists($this->table_name);

        $this->repository->createRepository();

        $this->assertTrue($this->repository->repositoryExists());
        $this->assertTableExists($this->table_name);
    }

    public function testGetConnection(): void
    {
        $this->assertInstanceOf(Connection::class, $this->repository->getConnection());
    }

    /**
     * Test for a got exception with invalid connection name.
     *
     * @return void
     */
    public function testGetWrongConnectionException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->repository = new Repository(
            $this->app->make('db')->connection('foo bar'),
            $this->table_name
        );

        $this->repository->getConnection();
    }

    public function testDelete(): void
    {
        $this->repository->createRepository();

        foreach (['foo', 'bar', $delete = 'bla bla'] as $migration_name) {
            $this->repository->insert($migration_name);
        }

        $this->repository->delete($delete);

        $this->assertNotContains($delete, $this->repository->migrations());
    }

    /**
     * Test migrations records inserting.
     *
     * @return void
     */
    public function testInsert(): void
    {
        $this->repository->createRepository();

        foreach ($migrations = ['foo', 'bar', 'udaff', 'bla bla'] as $migration_name) {
            $this->repository->insert($migration_name);
        }

        $inserted_migrations = $this->repository->migrations();

        $this->assertCount(count($migrations), $inserted_migrations);

        foreach ($migrations as $migration_name) {
            $this->assertContains($migration_name, $inserted_migrations);
        }
    }

    /**
     * Test migrations records clearing.
     *
     * @return void
     */
    public function testClear(): void
    {
        $this->repository->createRepository();

        foreach ($migrations = ['foo', 'bar'] as $migration_name) {
            $this->repository->insert($migration_name);
        }

        $inserted_migrations = $this->repository->migrations();

        $this->assertCount(\count($migrations), $inserted_migrations);

        $this->assertTrue($this->repository->clear());
        $this->assertEmpty($this->repository->migrations());

        $this->assertFalse($this->repository->clear());
    }

    /**
     * Assert that database has table.
     *
     * @param string      $table_name
     * @param string|null $connection
     *
     * @throws AssertionFailedError
     *
     * @return void
     */
    public function assertTableExists($table_name, $connection = null)
    {
        $this->assertTrue($this->tableExists($table_name, $connection));
    }

    /**
     * Assert that database has no table.
     *
     * @param string      $table_name
     * @param string|null $connection
     *
     * @throws AssertionFailedError
     *
     * @return void
     */
    public function assertTableNotExists($table_name, $connection = null)
    {
        $this->assertFalse($this->tableExists($table_name, $connection));
    }

    /**
     * @param string      $table_name
     * @param string|null $connection
     *
     * @return bool
     */
    protected function tableExists($table_name, $connection = null): bool
    {
        return $this->app->make('db')->connection($connection)->getSchemaBuilder()->hasTable($table_name);
    }
}
