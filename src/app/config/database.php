<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */
    'default' => env('DB_CONNECTION', 'pgsql'),

    // Migration Repository Table
    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lumen-ddd-sample'),
            'username' => env('DB_USERNAME', 'lumen-ddd-sample'),
            'password' => env('DB_PASSWORD', 'lumen-ddd-password'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
    ],

    'errors' => [
        'pdo' => [
            'HY000',
        ],
        /*
         * @link https://www.postgresql.org/docs/12/errcodes-appendix.html
         */
        'dbal' => [
            //  Connection Exceptions
            '08000',
            '08001',
            '08003',
            '08004',
            '08006',
            '08007',
            '08P01',
            //  Invalid Authorization Specification
            '28000',
            '28P01',
            // Insufficient Resources
            '53000',
            '53100',
            '53200',
            '53300',
            '53400',
            // Operator Intervention
            '57P01', // admin shut down
            '57P02', // crash shut down
            '57P03', // cannot_connect_now
            '57P04', // db dropped
            // Internal Error
            'XX000',
            'XX001',
            'XX002',
        ],
    ],
];
