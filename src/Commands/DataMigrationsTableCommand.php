<?php

namespace AvtoDev\DataMigrationsLaravel\Commands;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Config\Repository as ConfigRepository;
use AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider;

/**
 * Class DataMigrationsTableCommand.
 *
 * Command for crating migration file for data migrations.
 */
class DataMigrationsTableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'data-migrations:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migration for the data migrations repository';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * DataMigrationsTableCommand constructor.
     *
     * @param Filesystem $files
     * @param Composer   $composer
     *
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files    = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     *
     * @return void
     */
    public function handle()
    {
        /** @var ConfigRepository $config */
        $config = $this->laravel->make('config');

        $table_name = $config->get(sprintf('%s.table_name', DataMigrationsServiceProvider::getConfigRootKeyName()));

        $this->replaceMigration(
            $path = $this->createBaseMigration($table_name), $table_name, Str::studly($table_name)
        );

        $this->info(sprintf('Migration "%s" created successfully!', $path));

        $this->composer->dumpAutoloads();
    }

    /**
     * Replace the generated migration with the job table stub.
     *
     * @param string $path
     * @param string $table_name
     * @param string $table_class_name
     *
     * @return void
     */
    protected function replaceMigration($path, $table_name, $table_class_name)
    {
        $stub = str_replace(
            ['{{table_name}}', '{{table_class_name}}'],
            [$table_name, $table_class_name],
            $this->files->get(__DIR__ . '/stubs/migrations_data.stub')
        );

        $this->files->put($path, $stub);
    }

    /**
     * Create a base migration file for the table.
     *
     * @param string $table
     *
     * @return string
     */
    protected function createBaseMigration($table = 'config_values')
    {
        /** @var MigrationCreator $creator */
        $creator = $this->laravel->make('migration.creator');

        $path = empty($passed_path = $this->option('path'))
            ? $this->laravel->databasePath('migrations')
            : $passed_path;

        return $creator->create(
            'create_' . $table . '_table', $path
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, 'Path to the output directory.'],
        ];
    }
}
