<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Commands;

use Illuminate\Support\Arr;
use AvtoDev\DataMigrationsLaravel\ServiceProvider;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\Commands\StatusCommand
 */
class StatusCommandTest extends AbstractCommandTestCase
{
    /**
     * @var MigratorContract
     */
    protected $migrator;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->app->make(MigratorContract::class);
    }

    /**
     * Test regular command execution.
     *
     * @return void
     */
    public function testCommandExecution(): void
    {
        $not_migrated = ['2000_01_01_000020_simple_sql_data.sql'];
        $all          = Arr::flatten($this->migrator->source()->all());
        $filtered     = array_filter($all, function ($migration_name) use (&$not_migrated) {
            return ! in_array($migration_name, $not_migrated, true);
        });

        foreach ($filtered as $migration_name) {
            $this->migrator->repository()->insert($migration_name);
        }

        foreach ($not_migrated as $needed_migration_name) {
            $this->assertNotContains($needed_migration_name, $this->migrator->repository()->migrations());
        }

        $this->artisan($this->getCommandSignature());
        $output = $this->console()->output();

        foreach ($filtered as $item) {
            $this->assertRegExp("~Y\s+\|\s{$item}~", $output);
        }

        foreach ($not_migrated as $item) {
            $this->assertRegExp("~N\s+\|\s{$item}~", $output);
        }
    }

    /**
     * Test command execution without installed repository.
     *
     * @return void
     */
    public function testCommandExecutionWithoutRepositoryInstalled(): void
    {
        $this->migrator->repository()->deleteRepository();

        $this->artisan($this->getCommandSignature());
        $this->assertRegExp('~No migrations found.+repository not installed~i', $this->console()->output());
    }

    /**
     * Test command execution without migrations (locally and in repository).
     *
     * @return void
     */
    public function testCommandExecutionWithoutMigrations(): void
    {
        $this->config()->set(
            ServiceProvider::getConfigRootKeyName() . '.migrations_path',
            $out_directory = __DIR__ . '/../temp/storage/framework/testing' // <- empty directory
        );

        $this->artisan($this->getCommandSignature());

        $this->assertRegExp('~No migrations found~i', $this->console()->output());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandSignature(): string
    {
        return 'data-migrate:status';
    }
}
