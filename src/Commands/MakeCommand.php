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
    public function handle(SourceContract $source)
    {
        $result = $source->create($this->argument('name'), null, $this->option('connection'));

        if (\is_string($result)) {
            $this->line(sprintf('<info>Created Migration:</info> %s', $result));
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the migration.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection name.'],
        ];
    }
}
