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
    ],
    'queue' => true,
];