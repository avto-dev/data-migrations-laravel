<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Executors;

use PDOException;
use InvalidArgumentException;
use Illuminate\Database\Connection;
use AvtoDev\DataMigrationsLaravel\Tests\AbstractTestCase;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Executors\DatabaseRawQueryExecutor;

class DatabaseRawQueryExecutorTest extends AbstractTestCase
{
    /**
     * @var DatabaseRawQueryExecutor
     */
    protected $executor;

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

        $this->executor = new DatabaseRawQueryExecutor($this->app);
    }

    /**
     * Test instance implements contract.
     *
     * @return void
     */
    public function testInstance()
    {
        $this->assertInstanceOf(ExecutorContract::class, $this->executor);
    }

    /**
     * Test execute data.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->assertFalse($this->executor->execute(''));
        $this->assertFalse($this->executor->execute(null));

        /** @var Connection $connection */
        $connection = $this->app->make('db')->connection();
        $table_name = 'executor_table_test';

        $connection->getSchemaBuilder()->dropIfExists($table_name);

        $data = "CREATE TABLE {$table_name} (test varchar(255));";

        $this->assertTrue($this->executor->execute($data));

        $this->assertTrue($connection->getSchemaBuilder()->hasTable($table_name));
    }

    /**
     * Test exception throws when invalid connection passed.
     *
     * @return void
     */
    public function testExecuteWithExceptionOnUnknownConnection()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->executor->execute('SELECT * FROM sqlite_master WHERE 1=1;', 'foo bar');
    }

    /**
     * Test exception throws when invalid sql statement passed.
     *
     * @return void
     */
    public function testExecuteWithExceptionOnWrongSqlStatement()
    {
        $this->expectException(PDOException::class);

        $this->assertFalse($this->executor->execute('SELECT * FROM foo_bar;'));
    }
}
