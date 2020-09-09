<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Commands;

use Mockery as m;
use RuntimeException;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\Commands\UninstallCommand
 */
class UninstallCommandTest extends AbstractCommandTestCase
{
    /**
     * @var RepositoryContract
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(RepositoryContract::class);
    }

    /**
     * Test regular command execution.
     *
     * @return void
     */
    public function testCommandExecution(): void
    {
        $this->artisan($this->getCommandSignature());
        $this->assertStringContainsString('Repository removed successfully', $this->console()->output());

        $this->artisan($this->getCommandSignature());
        $this->assertStringContainsString('Repository already not exists in your database', $this->console()->output());
    }

    /**
     * Test command execution with uninstalled repository.
     *
     * @covers \AvtoDev\DataMigrationsLaravel\Repository
     *
     * @return void
     */
    public function testCommandExecutionWithUninstalledRepository(): void
    {
        $this->repository->deleteRepository();

        $this->artisan($this->getCommandSignature());
        $this->assertStringContainsString('Repository already not exists in your database', $this->console()->output());
    }

    /**
     * Test exception throwing.
     *
     * @return void
     */
    public function testExceptionThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('~Cannot remove repository in your database~');

        $mock = m::mock(clone $this->repository)
            ->makePartial()
            ->shouldReceive('deleteRepository')
            ->andReturnUsing(function () {
                throw new \Exception('Mockery is cool');
            })
            ->getMock();

        $this->app->instance(RepositoryContract::class, $mock);

        $this->artisan($this->getCommandSignature());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandSignature(): string
    {
        return 'data-migrate:uninstall';
    }
}
