<?php

return [

    /*
    | --------------------------------------------------------------------------
    | Database table name for storing migrations state
    | --------------------------------------------------------------------------
    |
    | You can override this value, but remember that in this case you must
    | change existing table name.
    */
    'table_name' => env('DATA_MIGRATIONS_TABLE_NAME', 'migrations_data'),

    /*
    | --------------------------------------------------------------------------
    | Database connection name used to store the migration table
    | --------------------------------------------------------------------------
    |
    | This value must be in config 'database.connections'. If selected 'null' -
    | will used the default connection.
    |
    | Available values: null|%connection_name%
    */
    'connection' => env('DATA_MIGRATIONS_CONNECTION', env('DB_CONNECTION', null)),

    /*
    | --------------------------------------------------------------------------
    | Path to the directory with data migrations files
    | --------------------------------------------------------------------------
    |
    | This path uses for searching migrations files.
    */
    'migrations_path' => env('DATA_MIGRATIONS_PATH', storage_path('data_migrations')),

    /*
    | --------------------------------------------------------------------------
    | Migrations 'executor' class
    | --------------------------------------------------------------------------
    |
    | This class must implement interface ExecutorContract.
    */
    'executor_class' => env(
        'DATA_MIGRATIONS_EXECUTOR_CLASS',
        AvtoDev\DataMigrationsLaravel\Executors\DatabaseRawQueryExecutor::class
    ),

];
