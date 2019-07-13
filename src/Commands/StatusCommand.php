<?php

declare(strict_types=1);

namespace AvtoDev\DataMigrationsLaravel\Commands;

use Illuminate\Console\Command;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class StatusCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'data-migrate:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the status of migrated data';

    /**
     * Execute the console command.
     *
     * @param RepositoryContract $repository
     * @param SourceContract     $source
     *
     * @return void
     */
    public function handle(RepositoryContract $repository, SourceContract $source): void
    {
        if (! $repository->repositoryExists()) {
            $this->error('No migrations found (repository not installed)');

            return;
        }

        $summary = array_unique(array_merge($migrated = $repository->migrations(), array_flatten($source->all())));

        if (\count($summary) > 0) {
            $table_rows = array_map(function ($migration_name) use (&$migrated) {
                return [
                    'ran'       => \in_array($migration_name, $migrated, true)
                        ? '<info>Y</info>'
                        : '<fg=red>N</fg=red>',
                    'migration' => $migration_name,
                ];
            }, $summary);

            $this->table(['Ran?', 'Data migration'], $table_rows);
        } else {
            $this->error('No migrations found');
        }
    }
}
