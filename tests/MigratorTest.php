<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;
use AvtoDev\DataMigrationsLaravel\Migrator;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;
use InvalidArgumentException;

class MigratorTest extends AbstractTestCase
{
    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrator = new Migrator(
            $this->app->make(RepositoryContract::class),
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
        $this->assertInstanceOf(Filesystem::class, $this->migrator->getFilesystem());
    }

    /**
     * Test migrations repository getter.
     *
     * @return void
     */
    public function testGetRepository()
    {
        $this->assertInstanceOf(RepositoryContract::class, $this->migrator->getRepository());
    }

    /**
     * Test migrations files getter for default connection.
     *
     * @return void
     */
    public function testGetMigrationsFilesForDefaultConnection()
    {
        $files       = $this->migrator->getMigrationsFiles();
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
        $files = $this->migrator->getMigrationsFiles('connection_2');
        $this->assertStringStartsWith('2000_01_01_000010', $files[0]->getFilename());

        $files = $this->migrator->getMigrationsFiles('connection_3');
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

        $this->migrator->getMigrationsFiles('foobar');
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
            . '_' . 'foo',
            $this->migrator->generateFileName('foo')
        );

        $this->assertEquals(
            '2010_02_03_036920_bar',
            $this->migrator->generateFileName('bar', Carbon::create(2010, 2, 3, 10, 15, 20))
        );

        $asserts = [
            'barBaz'      => 'barbaz',
            'Foo_bar'     => 'foo_bar',
            'foO bar 2'   => 'foo_bar_2',
            'foO &^% baz' => 'foo_baz',
        ];

        foreach ($asserts as $what => $with) {
            $this->assertStringEndsWith($with, $this->migrator->generateFileName($what));
        }
    }
}
