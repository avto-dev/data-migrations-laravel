<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use AvtoDev\DataMigrationsLaravel\Migrator;
use AvtoDev\DataMigrationsLaravel\Sources\Files;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use AvtoDev\DataMigrationsLaravel\Executors\DatabaseRawQueryExecutor;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\Migrator<extended>
 */
class MigratorTest extends AbstractTestCase
{
    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
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
    public function testGetSource(): void
    {
        $this->assertInstanceOf(SourceContract::class, $this->migrator->source());
    }

    /**
     * Test migrations repository getter.
     *
     * @return void
     */
    public function testGetRepository(): void
    {
        $this->assertInstanceOf(RepositoryContract::class, $this->migrator->repository());
    }

    /**
     * Test getter for non-migrated migrations.
     *
     * @return void
     */
    public function testNotMigrated(): void
    {
        $this->initRepositoryExcepts([
            $exclude_1 = '2000_01_01_000001_simple_sql_data.sql',
            $exclude_2 = '2000_01_01_000010_simple_sql_data.sql',
        ]);

        $this->assertEquals([
            ''             => [$exclude_1],
            'connection_2' => [$exclude_2],
        ], $this->migrator->needToMigrateList());
    }

    /**
     * Test migration method without passing connection name.
     *
     * @return void
     */
    public function testMigrateWithoutPassingConnectionName(): void
    {
        $this->initRepositoryExcepts($excludes = [
            $exclude_1 = '2000_01_01_000001_simple_sql_data.sql',
            $exclude_2 = '2000_01_01_000002_simple_sql_data_tarball.sql.gz',
        ]);

        $this->assertRepositoryHasNotMigrations($excludes);

        $this->assertEquals($excludes, $this->migrator->migrate());

        $this->assertRepositoryHasMigrations($excludes);

        $this->assertDatabaseHas('foo_table', ['id' => 10, 'data' => 'foo1', 'string' => 'bar2']);
        $this->assertDatabaseHas('foo_table', ['id' => 20, 'data' => 'bar1', 'string' => 'baz2']);
        $this->assertDatabaseHas('foo_table', ['id' => 1, 'data' => 'tarball-ed', 'string' => 'data']);
    }

    /**
     * Test migration method WITH passing connection name.
     *
     * @return void
     */
    public function testMigrateWithPassingConnection(): void
    {
        $this->initRepositoryExcepts($excludes = [
            $exclude = '2000_01_01_000010_simple_sql_data.sql',
        ]);

        $this->assertRepositoryHasNotMigrations($excludes);

        $this->assertEquals($excludes, $this->migrator->migrate($connection_name = 'connection_2'));

        $this->assertRepositoryHasMigrations($excludes);

        $this->assertDatabaseHas('foo_table2', [
            'id'     => 1,
            'data'   => 'connection',
            'string' => 'two',
        ], $connection_name);
    }

    /**
     * Test migration method passing closure for making migration process more interacts.
     *
     * @return void
     */
    public function testMigrateWithPassingClosure(): void
    {
        $this->initRepositoryExcepts($excludes = [
            $exclude_1 = '2000_01_01_000001_simple_sql_data.sql',
            $exclude_2 = '2000_01_01_000010_simple_sql_data.sql',
        ]);

        $closure_migrations_names = [];
        $closure_statuses         = [];
        $closure_current          = null;
        $closure_total            = null;
        $loop_counter             = 0;

        $this->assertRepositoryHasNotMigrations($excludes);

        $this->assertEquals($excludes, $migrated = $this->migrator->migrate(
            null,
            function ($migration_name, $status, $current, $total) use (
                &$closure_migrations_names,
                &$closure_statuses,
                &$closure_current,
                &$closure_total,
                $exclude_1,
                &$loop_counter
            ) {
                $closure_migrations_names[] = $migration_name;
                $closure_statuses[]         = $status;
                $closure_current            = $current;
                $closure_total              = $total;

                if ($migration_name === $exclude_1) {
                    switch ($loop_counter) {
                        case 0:
                            $this->assertEquals(Migrator::STATUS_MIGRATION_READ, $status);
                            break;

                        case 1:
                            $this->assertEquals(Migrator::STATUS_MIGRATION_STARTED, $status);
                            break;

                        case 2:
                            $this->assertEquals(Migrator::STATUS_MIGRATION_COMPLETED, $status);
                            break;

                        default:
                            throw new \Exception('Something wrong with counter');
                    }

                    $this->assertEquals(1, $closure_current);

                    $loop_counter++;
                } else {
                    $this->assertEquals(2, $closure_current);
                }

                $this->assertEquals($total, 2);
            }
        ));

        $this->assertGreaterThanOrEqual(2, $loop_counter);
        $this->assertEquals($excludes, $migrated);
        $this->assertCount(6, $closure_statuses);

        $this->assertRepositoryHasMigrations($excludes);
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
