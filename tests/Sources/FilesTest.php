<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Sources;

use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;
use AvtoDev\DataMigrationsLaravel\Sources\Files;
use AvtoDev\DataMigrationsLaravel\Tests\AbstractTestCase;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\Sources\Files<extended>
 */
class FilesTest extends AbstractTestCase
{
    /**
     * @var Files
     */
    protected $files;

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

        $this->files = new Files(
            $this->app->make('files'),
            __DIR__ . '/../stubs/data_migrations'
        );

        $this->assertInstanceOf(SourceContract::class, $this->files);
    }

    /**
     * Test filesystem getter.
     *
     * @return void
     */
    public function testGetFilesystem(): void
    {
        $this->assertInstanceOf(Filesystem::class, $this->files->filesystem());
    }

    /**
     * Test migrations files getter for default connection.
     *
     * @return void
     */
    public function testGetMigrationsFilesForDefaultConnection(): void
    {
        $migrations = $this->files->migrations();
        $names      = array_values($migrations);

        $this->assertNotEmpty($migrations);

        $this->assertEquals('2000_01_01_000001_simple_sql_data.sql', $names[0]);
        $this->assertEquals('2000_01_01_000002_simple_sql_data_tarball.sql.gz', $names[1]);
    }

    /**
     * Test migrations files getter for custom connections.
     *
     * @return void
     */
    public function testGetMigrationsFilesForCustomConnections(): void
    {
        $migrations_for_connection_2 = $this->files->migrations('connection_2');
        $this->assertEquals('2000_01_01_000010_simple_sql_data.sql', array_values($migrations_for_connection_2)[0]);

        $migrations_for_connection_3 = $this->files->migrations('connection_3');
        $this->assertEquals('2000_01_01_000020_simple_sql_data.sql', array_values($migrations_for_connection_3)[0]);
    }

    /**
     * Test migrations files getter for custom connections.
     *
     * @return void
     */
    public function testGetMigrationsFilesExceptionWithInvalidConnectionName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('~Directory.*does not exists~i');

        $this->files->migrations('foobar');
    }

    /**
     * Test migration file name generator.
     *
     * @return void
     */
    public function testGenerateFileName(): void
    {
        $now = Carbon::now();

        $this->assertEquals(
            $now->format('Y_m_d')
            . '_' . str_pad($now->secondsSinceMidnight(), 6, '0', STR_PAD_LEFT)
            . '_' . 'foo.sql',
            $this->files->generateFileName('foo')
        );

        $this->assertEquals(
            '2010_02_03_036920_bar.stub',
            $this->files->generateFileName('bar', Carbon::create(2010, 2, 3, 10, 15, 20), 'stub')
        );

        $asserts = [
            'barBaz'      => 'barbaz.sql',
            'Foo_bar'     => 'foo_bar.sql',
            'foO bar 2'   => 'foo_bar_2.sql',
            'foO &^% baz' => 'foo_baz.sql',
        ];

        foreach ($asserts as $what => $with) {
            $this->assertStringEndsWith($with, $this->files->generateFileName($what));
        }
    }

    /**
     * Test migration files creating.
     *
     * @return void
     */
    public function testCreate(): void
    {
        /** @var Filesystem $files */
        $files = $this->app->make('files');
        $path  = static::getTemporaryDirectoryPath() . DIRECTORY_SEPARATOR . 'test_migrations_creating';

        // Cleanup at first
        if ($files->isDirectory($path)) {
            $files->deleteDirectory($path);
        }

        $this->assertDirectoryNotExists($path);

        $migration_files = new Files($files, $path);

        $this->assertFileNotExists(
            $expected_path = $path . DIRECTORY_SEPARATOR . $migration_files->generateFileName($name = 'test_migration')
        );
        $result = $migration_files->create($name);
        $this->assertEquals($expected_path, $result);
        $this->assertFileExists($result);
        $this->assertStringEqualsFile($result, '');

        $result = $migration_files->create('some 2', null, null, $content = 'foo baz');
        $this->assertStringEqualsFile($result, $content);

        $result = $migration_files->create('some 2', null, $connection = 'foo_connection', $content = 'bar');
        $this->assertDirectoryExists($path . DIRECTORY_SEPARATOR . $connection);
        $this->assertEquals([$connection], $migration_files->connections());
        $this->assertStringEqualsFile($result, $content);

        $files->deleteDirectory($path);
    }

    /**
     * Test getter for available connections names inside work directory.
     *
     * @return void
     */
    public function testGetConnectionsNames(): void
    {
        $this->assertEquals(['connection_2', 'connection_3'], $this->files->connections());
    }

    /**
     * Test content getter.
     *
     * @return void
     */
    public function testGetContent(): void
    {
        $files_list = array_values($this->files->migrations());
        $this->assertStringStartsWith('CREATE TABLE foo_table', $this->files->get($files_list[0]));
        $this->assertStringStartsWith('INSERT INTO foo_table', $this->files->get($files_list[1]));

        $files_list = array_values($this->files->migrations($connection_name = 'connection_2'));
        $this->assertStringStartsWith('CREATE TABLE foo_table2', $this->files->get($files_list[0], $connection_name));

        $files_list = array_values($this->files->migrations($connection_name = 'connection_3'));
        $this->assertStringStartsWith('CREATE TABLE foo_table3', $this->files->get($files_list[0], $connection_name));
    }

    /**
     * Test migration name to the file path converting (and back).
     *
     * @return void
     */
    public function testMigrationNameToPathConverting(): void
    {
        $this->files = new Files($this->app->make('files'), '/foo/');

        $this->assertEquals('/foo/bar', $this->files->nameToPath('bar'));
        $this->assertEquals('/foo/baz.sql', $this->files->nameToPath('/baz.sql'));

        $this->assertEquals('bar', $this->files->pathToName('/foo/bar'));
        $this->assertEquals('baz.sql', $this->files->pathToName('/foo/baz.sql'));
    }

    /**
     * Teat all migrations getter method.
     *
     * @return void
     */
    public function testAll(): void
    {
        $all = $this->files->all();

        foreach (['', 'connection_2', 'connection_3'] as $key_name) {
            $this->assertArrayHasKey($key_name, $all);
        }

        $this->assertEquals('2000_01_01_000001_simple_sql_data.sql', $all[''][0]);
        $this->assertEquals('2000_01_01_000002_simple_sql_data_tarball.sql.gz', $all[''][1]);

        $this->assertEquals('2000_01_01_000010_simple_sql_data.sql', $all['connection_2'][0]);

        $this->assertEquals('2000_01_01_000020_simple_sql_data.sql', $all['connection_3'][0]);
    }
}
