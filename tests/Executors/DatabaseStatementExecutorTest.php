<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Executors;

use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;
use AvtoDev\DataMigrationsLaravel\Executors\DatabaseStatementExecutor;
use AvtoDev\DataMigrationsLaravel\Tests\AbstractTestCase;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class DatabaseStatementExecutorTest extends AbstractTestCase
{
    /**
     * @var DatabaseStatementExecutor
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
    public function setUp()
    {
        parent::setUp();

        $this->executor = new DatabaseStatementExecutor();
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
        /** @var Connection $connection */
        $connection = DB::connection();

        $table_name = 'executor_table_test';
        $connection->getSchemaBuilder()->dropIfExists($table_name);

        $data = "CREATE TABLE {$table_name} (test varchar(255));";

        $this->executor->execute($data);

        $this->assertTrue($connection->getSchemaBuilder()->hasTable($table_name));
    }
}
