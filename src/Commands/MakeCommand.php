<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;

class MakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:data-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new data migration file';

    /**
     * Execute the console command.
     *
     * @param SourceContract $source
     *
     * @return void
     */
    public function handle(SourceContract $source): void
    {
        $result = $source->create($this->getMigrationName(), null, $this->getConnectionName());

        if (\is_string($result)) {
            $this->line("<info>Created Migration:</info> {$result}");
        }
    }

    /**
     * @return string
     */
    protected function getMigrationName(): string
    {
        $name = $this->argument('name');

        return \is_string($name)
            ? $name
            : 'noname';
    }

    /**
     * @return string|null
     */
    protected function getConnectionName(): ?string
    {
        $connection = $this->option('connection');

        return \is_string($connection)
            ? $connection
            : null;
    }

    /**
     * Get the console command arguments.
     *
     * @return array<array<mixed>>
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the migration.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array<array<mixed>>
     */
    protected function getOptions(): array
    {
        return [
            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection name.'],
        ];
    }
}
