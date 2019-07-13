<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Commands;

use Mockery as m;
use Illuminate\Contracts\Console\Kernel;
use AvtoDev\DataMigrationsLaravel\Commands\MigrateCommand;
use AvtoDev\DataMigrationsLaravel\Contracts\MigratorContract;

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
    public function testRepositoryAutoCreation()
    {
        $this->migrator->repository()->deleteRepository();

        $this->artisan($this->getCommandSignature());
        $this->assertContains('Repository created successfully', $this->console()->output());
    }

    /**
     * Test regular command execution.
     *
     * @return void
     */
    public function testCommandExecution()
    {
        $not_migrated = ['2000_01_01_000020_simple_sql_data.sql'];
        $all          = array_flatten($this->migrator->source()->all());
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
            $this->assertContains($needed_migration_name, $output);
            $this->assertContains($needed_migration_name, $this->migrator->repository()->migrations());
        }

        // And at last
        $this->artisan($this->getCommandSignature());
        $this->assertContains('Nothing to migrate', $this->console()->output());
    }

    /**
     * Test '--force' flag working.
     *
     * @return void
     */
    public function testForceFlagWorking()
    {
        $this->setAppEnvironment('production');
        $command = m::mock(sprintf('%s[%s]', MigrateCommand::class, $what = 'confirm'));
        $command->shouldReceive($what)->once()->andReturn(true);
        $this->app->make(Kernel::class)->registerCommand($command);

        $this->artisan($this->getCommandSignature());
        $output = $this->console()->output();

        $this->assertContains('Application In Production', $output);
        $this->assertContains('Migrated', $output);
    }

    /**
     * Test failing on production env without '--force' flag.
     *
     * @return void
     */
    public function testExecutionOnProductionFailed()
    {
        $this->setAppEnvironment('production');
        $command = m::mock(sprintf('%s[%s]', MigrateCommand::class, $what = 'confirm'));
        $command->shouldReceive($what)->once()->andReturn(false);
        $this->app->make(Kernel::class)->registerCommand($command);

        $this->artisan($this->getCommandSignature());
        $output = $this->console()->output();

        $this->assertContains('Application In Production', $output);
        $this->assertContains('Command Cancelled', $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandSignature()
    {
        return 'data-migrate';
    }
}
