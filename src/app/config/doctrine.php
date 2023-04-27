<?php

use Infrastructure\Persistence\Doctrine\Filters\SoftDeleteFilter;
use Infrastructure\Persistence\Doctrine\Types;

return [
    'managers' => [
        'admin_panel' => [
            'dev'           => env('APP_DEBUG', false),
            'meta'          => env('DOCTRINE_METADATA', 'SimplifiedXml'),
            'connection'    => env('DB_CONNECTION', 'pgsql'),
            'namespaces'    => [],
            'paths'         => [
                base_path('../Infrastructure/Persistence/Doctrine/Mapping/AdminPanel/Accounts') => 'Domains\AdminPanel\Models\Accounts',
                base_path('../Infrastructure/Persistence/Doctrine/Mapping/AdminPanel/Sales') => 'Domains\AdminPanel\Models\Sales',
             ],
            'repository'    => Doctrine\ORM\EntityRepository::class,
            'proxies'       => [
                'namespace'     => false,
                'path'          => storage_path('proxies'),
                'auto_generate' => env('DOCTRINE_PROXY_AUTOGENERATE', false)
            ],
            /*
            |--------------------------------------------------------------------------
            | Doctrine events
            |--------------------------------------------------------------------------
            |
            | The listener array expects the key to be a Doctrine event
            | e.g. Doctrine\ORM\Events::onFlush
            |
            */
            'events'        => [
                'listeners'   => [],
                'subscribers' => []
            ],
            'filters'       => [
                'soft-delete' => SoftDeleteFilter::class,
            ],

            /*
            |--------------------------------------------------------------------------
            | Doctrine mapping types
            */
            'mapping_types' => [
                'jsonb' => 'jsonb',
                '_jsonb' => 'jsonb[]',
                'jsonb[]' => 'jsonb[]',
                '_int2' => 'smallint[]',
                'smallint[]' => 'smallint[]',
                '_int4' => 'integer[]',
                'integer[]' => 'integer[]',
                '_int8' => 'bigint',
                'bigint[]' => 'bigint[]',
                '_text' => 'text[]',
                'text[]' => 'text[]',
                'AggregateRootId' => Types\Common\DoctrineAggregateRootId::NAME,
            ],
        ],

        'default' => [
            'dev'           => env('APP_DEBUG', false),
            'meta'          => env('DOCTRINE_METADATA', 'SimplifiedXml'),
            'connection'    => env('DB_CONNECTION', 'pgsql'),
            'namespaces'    => [],
            'paths'         => [
                base_path('../Infrastructure/Persistence/Doctrine/Mapping/Accounts/Company') => 'Domains\Accounts\Models\Company',
                base_path('../Infrastructure/Persistence/Doctrine/Mapping/Accounts/User') => 'Domains\Accounts\Models\User',

                base_path('../Infrastructure/Persistence/Doctrine/Mapping/Sales/Lead') => 'Domains\Sales\Models\Lead',
                base_path('../Infrastructure/Persistence/Doctrine/Mapping/Sales/Workflow') => 'Domains\Sales\Models\Workflow',

            ],
            'repository'    => Doctrine\ORM\EntityRepository::class,
            'proxies'       => [
                'namespace'     => false,
                'path'          => storage_path('proxies'),
                'auto_generate' => env('DOCTRINE_PROXY_AUTOGENERATE', false),
            ],
            /*
            |--------------------------------------------------------------------------
            | Doctrine events
            |--------------------------------------------------------------------------
            |
            | The listener array expects the key to be a Doctrine event
            | e.g. Doctrine\ORM\Events::onFlush
            |
            */
            'events'        => [
                'listeners'   => [],
                'subscribers' => []
            ],
            'filters'       => [
                'soft-delete' => SoftDeleteFilter::class,
            ],

            /*
            |--------------------------------------------------------------------------
            | Doctrine mapping types
            */
            'mapping_types' => [
                'jsonb' => 'jsonb',
                '_jsonb' => 'jsonb[]',
                'jsonb[]' => 'jsonb[]',
                '_int2' => 'smallint[]',
                'smallint[]' => 'smallint[]',
                '_int4' => 'integer[]',
                'integer[]' => 'integer[]',
                '_int8' => 'bigint',
                'bigint[]' => 'bigint[]',
                '_text' => 'text[]',
                'text[]' => 'text[]',

                'CompanyAccountId' => Types\Accounts\DoctrineCompanyAccountId::NAME,

                'LeadId' => Types\Sales\DoctrineLeadId::NAME,

                'Password' => Types\Accounts\DoctrinePassword::NAME,
                'Price' => Types\Sales\DoctrinePrice::NAME,

                'Revenue' => Types\Sales\DoctrineRevenue::NAME,
                'Role' => Types\Common\DoctrineRole::NAME,

                'StageId' => Types\Sales\DoctrineStageId::NAME,
                'StageType' => Types\Sales\DoctrineStage::NAME,

                'UserCompanyAccountId' => Types\Accounts\DoctrineUserCompanyAccountId::NAME,
                'UserCompanyAccountStatus' => Types\Accounts\DoctrineUserCompanyAccountStatus::NAME,
                'UserId' => Types\Accounts\DoctrineUserId::NAME,
                'WorkflowId' => Types\Sales\DoctrineWorkflowId::NAME,
            ],
        ],
    ],

    /*
   |--------------------------------------------------------------------------
   | Doctrine custom types
   |--------------------------------------------------------------------------
   */
    'custom_types' => [
        'jsonb' => MartinGeorgiev\Doctrine\DBAL\Types\Jsonb::class,
        'jsonb[]' => MartinGeorgiev\Doctrine\DBAL\Types\JsonbArray::class,
        'text[]' => MartinGeorgiev\Doctrine\DBAL\Types\TextArray::class,
        'bigint[]' => MartinGeorgiev\Doctrine\DBAL\Types\BigIntArray::class,
        'integer[]' => MartinGeorgiev\Doctrine\DBAL\Types\IntegerArray::class,
        'smallint[]' => MartinGeorgiev\Doctrine\DBAL\Types\SmallIntArray::class,

        # accounts
        Types\Accounts\DoctrineCompanyAccountId::NAME => Types\Accounts\DoctrineCompanyAccountId::class,
        Types\Accounts\DoctrinePassword::NAME => Types\Accounts\DoctrinePassword::class,
        Types\Accounts\DoctrineUserId::NAME => Types\Accounts\DoctrineUserId::class,
        Types\Accounts\DoctrineUserCompanyAccountId::NAME => Types\Accounts\DoctrineUserCompanyAccountId::class,
        Types\Accounts\DoctrineUserCompanyAccountStatus::NAME => Types\Accounts\DoctrineUserCompanyAccountStatus::class,


        # common
        Types\Common\DoctrineAggregateRootId::NAME => Types\Common\DoctrineAggregateRootId::class,
        Types\Common\DoctrineRole::NAME => Types\Common\DoctrineRole::class,

        # sales
        Types\Sales\DoctrineLeadId::NAME => Types\Sales\DoctrineLeadId::class,
        Types\Sales\DoctrinePrice::NAME => Types\Sales\DoctrinePrice::class,
        Types\Sales\DoctrineRevenue::NAME => Types\Sales\DoctrineRevenue::class,
        Types\Sales\DoctrineStage::NAME => Types\Sales\DoctrineStage::class,
        Types\Sales\DoctrineStageId::NAME => Types\Sales\DoctrineStageId::class,
        Types\Sales\DoctrineWorkflowId::NAME => Types\Sales\DoctrineWorkflowId::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Doctrine Extensions
    |--------------------------------------------------------------------------
    */
    'extensions'                 => [],
    /*
    |--------------------------------------------------------------------------
    | DQL custom datetime functions
    |--------------------------------------------------------------------------
    */
    'custom_datetime_functions'  => [],
    /*
    |--------------------------------------------------------------------------
    | DQL custom numeric functions
    |--------------------------------------------------------------------------
    */
    'custom_numeric_functions'   => [],
    /*
    |--------------------------------------------------------------------------
    | DQL custom string functions
    |--------------------------------------------------------------------------
    */
    'custom_string_functions'    => [
        # alternative implementation of ALL() and ANY() where subquery is not required, useful for arrays
        'ALL_OF' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\All::class,
        'ANY_OF' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Any::class,

        # operators for working with array and json(b) data
        'CONTAINS' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Contains::class,
        'IS_CONTAINED_BY' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\IsContainedBy::class,
        'OVERLAPS' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Overlaps::class,
        'GREATEST' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Greatest::class,
        'LEAST' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Least::class,

        # array specific functions
        'IN_ARRAY' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\InArray::class,
        'ARRAY' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Arr::class,
        'ARRAY_APPEND' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ArrayAppend::class,
        'ARRAY_CARDINALITY' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ArrayCardinality::class,
        'ARRAY_CAT' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ArrayCat::class,
        'ARRAY_PREPEND' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ArrayPrepend::class,
        'ARRAY_REMOVE' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ArrayRemove::class,
        'ARRAY_REPLACE' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ArrayReplace::class,
        'ARRAY_TO_STRING' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ArrayToString::class,
        'STRING_TO_ARRAY' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\StringToArray::class,
        'UNNEST' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Unnest::class,

        # json specific functions
        'JSON_GET_FIELD' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonGetField::class,
        'JSON_GET_FIELD_AS_TEXT' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonGetFieldAsText::class,
        'JSON_GET_FIELD_AS_INTEGER' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonGetFieldAsInteger::class,
        'JSON_GET_OBJECT' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonGetObject::class,
        'JSON_GET_OBJECT_AS_TEXT' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonGetObjectAsText::class,

        # jsonb specific functions
        'JSONB_ARRAY_ELEMENTS' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonbArrayElements::class,
        'JSONB_ARRAY_ELEMENTS_TEXT' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonbArrayElementsText::class,
        'JSONB_ARRAY_LENGTH' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonbArrayLength::class,
        'JSONB_EACH' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonbEach::class,
        'JSONB_EACH_TEXT' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonbEachText::class,
        'JSONB_EXISTS' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonbExists::class,
        'JSONB_OBJECT_KEYS' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\JsonbObjectKeys::class,

        # text search specific
        'TO_TSQUERY' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ToTsquery::class,
        'TO_TSVECTOR' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\ToTsvector::class,
        'TSMATCH' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Tsmatch::class,

        # other operators
        'ILIKE' => MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\Ilike::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Register custom hydrators
    |--------------------------------------------------------------------------
    */
    'custom_hydration_modes'     => [
        // e.g. 'hydrationModeName' => MyHydrator::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Enable query logging with laravel file logging,
    | debugbar, clockwork or an own implementation.
    | Setting it to false, will disable logging
    |
    | Available:
    | - LaravelDoctrine\ORM\Loggers\LaravelDebugbarLogger
    | - LaravelDoctrine\ORM\Loggers\ClockworkLogger
    | - LaravelDoctrine\ORM\Loggers\FileLogger
    |--------------------------------------------------------------------------
    */
    'logger' => env('DOCTRINE_LOGGER', false),
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configure meta-data, query and result caching here.
    | Optionally you can enable second level caching.
    |
    | Available: apc|array|file|memcached|redis|void
    |
    */
    'cache' => [
        'second_level'     => false,
        'default'          => env('DOCTRINE_CACHE', 'void'),
        'namespace'        => null,
        'metadata'         => [
            'driver'       => env('DOCTRINE_METADATA_CACHE', env('DOCTRINE_CACHE', 'void')),
            'namespace'    => null,
        ],
        'query'            => [
            'driver'       => env('DOCTRINE_QUERY_CACHE', env('DOCTRINE_CACHE', 'void')),
            'namespace'    => null,
        ],
        'result'           => [
            'driver'       => env('DOCTRINE_RESULT_CACHE', env('DOCTRINE_CACHE', 'void')),
            'namespace'    => null,
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Gedmo extensions
    |--------------------------------------------------------------------------
    |
    | Settings for Gedmo extensions
    | If you want to use this you will have to require
    | laravel-doctrine/extensions in your composer.json
    |
    */
    'gedmo'                      => [
        'all_mappings' => false
    ],
    /*
     |--------------------------------------------------------------------------
     | Validation
     |--------------------------------------------------------------------------
     |
     |  Enables the Doctrine Presence Verifier for Validation
     |
     */
    'doctrine_presence_verifier' => true,

    /*
     |--------------------------------------------------------------------------
     | Notifications
     |--------------------------------------------------------------------------
     |
     |  Doctrine notifications channel
     |
     */
    'notifications'              => [
        'channel' => 'database'
    ]
];
