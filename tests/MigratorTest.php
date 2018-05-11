<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use AvtoDev\DataMigrationsLaravel\Executors\DatabaseRawQueryExecutor;
use AvtoDev\DataMigrationsLaravel\Migrator;
use AvtoDev\DataMigrationsLaravel\Sources\Files;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class MigratorTest extends AbstractTestCase
{
    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrator = new Migrator(
            $this->app->make(RepositoryContract::class),
            new Files($this->app->make('files'), __DIR__ . '/stubs/data_migrations'),
            new DatabaseRawQueryExecutor($this->app)
        );
    }

    /**
     * Test source getter.
     *
     * @return void
     */
    public function testGetSource()
    {
        $this->assertInstanceOf(SourceContract::class, $this->migrator->source());
    }

    /**
     * Test migrations repository getter.
     *
     * @return void
     */
    public function testGetRepository()
    {
        $this->assertInstanceOf(RepositoryContract::class, $this->migrator->repository());
    }

    /**
     * Test getter for non-migrated migrations.
     *
     * @return void
     */
    public function testNotMigrated()
    {
        $this->initRepositoryExcepts([
            $exclude_1 = '2000_01_01_000001_simple_sql_data.sql',
            $exclude_2 = '2000_01_01_000010_simple_sql_data.sql',
        ]);

        $this->assertEquals([
            ''             => [$exclude_1],
            'connection_2' => [$exclude_2],
        ], $this->migrator->notMigrated());
    }

    public function testMigrateWithoutPassingConnectionName()
    {
        $this->initRepositoryExcepts($excludes = [
            $exclude_1 = '2000_01_01_000001_simple_sql_data.sql',
            $exclude_2 = '2000_01_01_000002_simple_sql_data_tarball.sql.gz',
        ]);

        $this->assertRepositoryHasNotMigrations($excludes);

        $this->assertEquals($excludes, $this->migrator->migrate());

        $this->assertRepositoryHasMigrations($excludes);

        $this->assertDatabaseHas('foo_table', ['id' => 10, 'data' => 'foo1']);
    }

    /**
     * Assert that migrations repository has passed migrations names.
     *
     * @param string[] $migrations_names
     */
    protected function assertRepositoryHasMigrations(array $migrations_names)
    {
        $in_repository = $this->migrator->repository()->migrations();

        foreach ($migrations_names as $migration_name) {
            $this->assertContains($migration_name, $in_repository);
        }
    }

    /**
     * Assert that migrations repository has NO passed migrations names.
     *
     * @param string[] $migrations_names
     */
    protected function assertRepositoryHasNotMigrations(array $migrations_names)
    {
        $in_repository = $this->migrator->repository()->migrations();

        foreach ($migrations_names as $migration_name) {
            $this->assertNotContains($migration_name, $in_repository);
        }
    }

    /**
     * Initialize migrator repository without passed migrations.
     *
     * @param string[] $migrations_names
     *
     * @return Migrator
     */
    protected function initRepositoryExcepts(array $migrations_names)
    {
        $all      = array_flatten($this->migrator->source()->all());
        $filtered = array_filter($all, function ($migration_name) use (&$migrations_names) {
            return ! in_array($migration_name, $migrations_names, true);
        });

        foreach ($filtered as $migration_name) {
            $this->migrator->repository()->insert($migration_name);
        }

        $this->assertRepositoryHasNotMigrations($migrations_names);

        return $this->migrator;
    }
}
