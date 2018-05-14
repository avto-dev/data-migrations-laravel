<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Commands;

use Mockery as m;
use RuntimeException;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class UninstallCommandTest extends AbstractCommandTestCase
{
    /**
     * @var RepositoryContract
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(RepositoryContract::class);
    }

    /**
     * Test regular command execution.
     *
     * @return void
     */
    public function testCommandExecution()
    {
        $this->artisan($this->getCommandSignature());
        $this->assertContains('Repository removed successfully', $this->console()->output());

        $this->artisan($this->getCommandSignature());
        $this->assertContains('Repository already not exists in your database', $this->console()->output());
    }

    /**
     * Test command execution with uninstalled repository.
     *
     * @return void
     */
    public function testCommandExecutionWithUninstalledRepository()
    {
        $this->repository->deleteRepository();

        $this->artisan($this->getCommandSignature());
        $this->assertContains('Repository already not exists in your database', $this->console()->output());
    }

    /**
     * Test exception throwing.
     *
     * @return void
     */
    public function testExceptionThrows()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('~Cannot remove repository in your database~');

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
    protected function getCommandSignature()
    {
        return 'data-migrate:uninstall';
    }
}
