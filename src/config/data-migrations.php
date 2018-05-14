<?php

return [

    /*
    | --------------------------------------------------------------------------
    | Database table name in which the migration data is stored
    | --------------------------------------------------------------------------
    |
    | You can override this value, but remember that in this case you must change
    | existing table name.
    */
    'table_name' => env('DATA_MIGRATIONS_TABLE_NAME', 'migrations_data'),

    /*
    | --------------------------------------------------------------------------
    | Database connection name used to store the migration table
    | --------------------------------------------------------------------------
    |
    | This value must be in config 'database.connections'. If selected 'null' value
    | that will use the default connection.
    |
    | Available values: null|%connection_name%
    */
    'connection' => env('DATA_MIGRATIONS_CONNECTION', env('DB_CONNECTION', null)),

    /*
    | --------------------------------------------------------------------------
    | Path to directory with data migration files
    | --------------------------------------------------------------------------
    |
    | The specified path will search for data migration files.
    */
    'migrations_path' => env('DATA_MIGRATIONS_PATH', storage_path('data_migrations')),

    /*
    | --------------------------------------------------------------------------
    | Class for execute migrations
    | --------------------------------------------------------------------------
    |
    | This class must implement ExecutorContract.
    */
    'executor_class' => env(
        'DATA_MIGRATIONS_EXECUTOR_CLASS',
        AvtoDev\DataMigrationsLaravel\Executors\DatabaseRawQueryExecutor::class
    ),

];
