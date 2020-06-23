<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Commands;

use RuntimeException;
use AvtoDev\DataMigrationsLaravel\ServiceProvider;

/**
 * @covers \AvtoDev\DataMigrationsLaravel\Commands\MakeCommand
 */
class MakeCommandTest extends AbstractCommandTestCase
{
    /**
     * Test regular command execution.
     *
     * @return void
     */
    public function testCommandExecution(): void
    {
        $this->config()->set(
            ServiceProvider::getConfigRootKeyName() . '.migrations_path',
            $out_directory = __DIR__ . '/../temp/storage'
        );

        $this->artisan($this->getCommandSignature(), [
            'name' => 'Some test',
        ]);

        $this->assertRegExp('~Created Migration.*some_test\.sql~', $this->console()->output());

        $this->artisan($this->getCommandSignature(), [
            'name'         => 'Some test 2',
            '--connection' => $connection = 'foobar',
        ]);

        $this->assertRegExp("~Created Migration.*{$connection}\\/[\S]*some_test_2.sql~", $this->console()->output());
    }

    /**
     * Test command execution without passing name.
     *
     * @return void
     */
    public function testCommandExecutionFailedWithoutName(): void
    {
        $this->expectException(RuntimeException::class);

        $this->artisan($this->getCommandSignature());
        $this->assertRegExp('~missing.*name~', $this->console()->output());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandSignature(): string
    {
        return 'make:data-migration';
    }
}
