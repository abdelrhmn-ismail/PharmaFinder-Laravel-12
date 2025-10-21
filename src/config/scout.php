<?php

return [
    'driver' => 'tntsearch',
    'tntsearch' => [
        'storage'  => storage_path('app/tntsearch'), //place where the index files will be stored
        'fuzziness' => env('TNTSEARCH_FUZZINESS', true),
        'fuzzy' => [
            'prefix_length' => 2,
            'max_expansions' => 50,
            'distance' => 2
        ],
        'asYouType' => false,
        'searchBoolean' => env('TNTSEARCH_BOOLEAN', false),
        'maxDocs' => env('TNTSEARCH_MAX_DOCS', 500),
        'mysql' => [
            'host'     => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_DATABASE', 'pharmafinder_test'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8mb4'
        ],
    ],
    'queue' => true,
];