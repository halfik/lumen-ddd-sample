<?php

return [
    'default' => [
        'table'     => 'migrations',
        'directory' => 'Infrastructure'
            . DIRECTORY_SEPARATOR . 'Persistence'
            . DIRECTORY_SEPARATOR . 'Database'
            . DIRECTORY_SEPARATOR . 'Migrations',

        'namespace' => 'Infrastructure\\Persistence\\Database\\Migrations',
        'schema'    => [
            'filter' => '/^(?!password_resets|failed_jobs).*$/'
        ],
        'version_column_length' => 14
    ],
];
