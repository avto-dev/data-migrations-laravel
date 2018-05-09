<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use AvtoDev\DataMigrationsLaravel\MigrationsFiles;

class MigrationsFilesTest extends AbstractTestCase
{
    /**
     * @var MigrationsFiles
     */
    protected $migration_files;

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

        $this->migration_files = new MigrationsFiles(
            $this->app->make('files'),
            __DIR__ . '/stubs/data_migrations'
        );
    }

    /**
     * Test filesystem getter.
     *
     * @return void
     */
    public function testGetFilesystem()
    {
        $this->assertInstanceOf(Filesystem::class, $this->migration_files->getFilesystem());
    }

    /**
     * Test migrations files getter for default connection.
     *
     * @return void
     */
    public function testGetMigrationsFilesForDefaultConnection()
    {
        $files       = $this->migration_files->getMigrationsFiles();
        $files_names = array_map(function (SplFileInfo $file) {
            return $file->getFilename();
        }, $files);

        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $this->assertInstanceOf(SplFileInfo::class, $file);
        }

        foreach ($files_names as $file_name) {
            $this->assertTrue(
                Str::startsWith($file_name, ['2000_01_01_000001', '2000_01_01_000002', '2000_01_01_000003'])
            );
        }
    }

    /**
     * Test migrations files getter for custom connections.
     *
     * @return void
     */
    public function testGetMigrationsFilesForCustomConnections()
    {
        $files = $this->migration_files->getMigrationsFiles('connection_2');
        $this->assertStringStartsWith('2000_01_01_000010', $files[0]->getFilename());

        $files = $this->migration_files->getMigrationsFiles('connection_3');
        $this->assertStringStartsWith('2000_01_01_000020', $files[0]->getFilename());
    }

    /**
     * Test migrations files getter for custom connections.
     *
     * @return void
     */
    public function testGetMigrationsFilesExceptionWithInvalidConnectionName()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->migration_files->getMigrationsFiles('foobar');
    }

    /**
     * Test migration file name generator.
     *
     * @return void
     */
    public function testGenerateFileName()
    {
        $now = Carbon::now();

        $this->assertEquals(
            $now->format('Y_m_d')
            . '_' . str_pad($now->secondsSinceMidnight(), 6, '0', STR_PAD_LEFT)
            . '_' . 'foo.sql',
            $this->migration_files->generateFileName('foo')
        );

        $this->assertEquals(
            '2010_02_03_036920_bar.stub',
            $this->migration_files->generateFileName('bar', Carbon::create(2010, 2, 3, 10, 15, 20), 'stub')
        );

        $asserts = [
            'barBaz'      => 'barbaz.sql',
            'Foo_bar'     => 'foo_bar.sql',
            'foO bar 2'   => 'foo_bar_2.sql',
            'foO &^% baz' => 'foo_baz.sql',
        ];

        foreach ($asserts as $what => $with) {
            $this->assertStringEndsWith($with, $this->migration_files->generateFileName($what));
        }
    }

    /**
     * Test migration files creating.
     *
     * @return void
     */
    public function testCreate()
    {
        /** @var Filesystem $files */
        $files = $this->app->make('files');
        $path  = static::getTemporaryDirectoryPath() . DIRECTORY_SEPARATOR . 'test_migrations_creating';

        // Cleanup at first
        if ($files->isDirectory($path)) {
            $files->deleteDirectory($path);
        }

        $this->assertDirectoryNotExists($path);

        $migration_files = new MigrationsFiles($files, $path);

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
        $this->assertEquals([$connection], $migration_files->getConnectionsNames());
        $this->assertStringEqualsFile($result, $content);

        $files->deleteDirectory($path);
    }

    /**
     * Test getter for available connections names inside work directory.
     *
     * @return void
     */
    public function testGetConnectionsNames()
    {
        $this->assertEquals(['connection_2', 'connection_3'], $this->migration_files->getConnectionsNames());
    }
}
