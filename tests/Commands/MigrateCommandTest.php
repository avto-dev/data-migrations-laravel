<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Commands;

use Mockery as m;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Console\Kernel;
use AvtoDev\DataMigrationsLaravel\Commands\MigrateCommand;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\Commands\MigrateCommand
 */
class MigrateCommandTest extends AbstractCommandTestCase
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
     * Repository must be created automatically.
     *
     * @return void
     */
    public function testRepositoryAutoCreation(): void
    {
        $this->migrator->repository()->deleteRepository();

        $this->artisan($this->getCommandSignature());
        $this->assertStringContainsString('Repository created successfully', $this->console()->output());
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

        foreach ($not_migrated as $needed_migration_name) {
            $this->assertStringContainsString($needed_migration_name, $output);
            $this->assertContains($needed_migration_name, $this->migrator->repository()->migrations());
        }

        // And at last
        $this->artisan($this->getCommandSignature());
        $this->assertStringContainsString('Nothing to migrate', $this->console()->output());
    }

    /**
     * Test '--force' flag working.
     *
     * @return void
     */
    public function testForceFlagWorking(): void
    {
        $this->setAppEnvironment('production');
        $command = m::mock(sprintf('%s[%s]', MigrateCommand::class, $what = 'confirmToProceed'));
        $command->shouldReceive($what)->andReturnTrue();
        $this->app->make(Kernel::class)->registerCommand($command);

        $this->artisan($this->getCommandSignature());
        $output = $this->console()->output();

        $this->assertStringContainsString('Migrated', $output);
    }

    /**
     * Test failing on production env without '--force' flag.
     *
     * @return void
     */
    public function testExecutionOnProductionFailed(): void
    {
        $this->setAppEnvironment('production');
        $command = m::mock(sprintf('%s[%s]', MigrateCommand::class, $what = 'confirmToProceed'));
        $command->shouldReceive($what)->andReturnFalse();
        $this->app->make(Kernel::class)->registerCommand($command);

        $this->artisan($this->getCommandSignature());
        $output = $this->console()->output();

        $this->assertEmpty($output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandSignature(): string
    {
        return 'data-migrate';
    }
}
