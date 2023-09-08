<?php

declare(strict_types=1);

return [
    'salt' => env('INDEX_SALT'),
    'iterations' => env('SEARCH_HASH_ITERATIONS', 1000),
    'pbkdf2' => [
        'algo' => 'sha3-512',
    ],

    'queue' => [
        'connection' => env('SEARCH_HASH_JOB_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'queue_name' => env('SEARCH_HASH_JOB_QUEUE_NAME', 'default'),
        'delayInSeconds' => env('SEARCH_HASH_JOB_DELAY_SECONDS', '0'),
    ],
];
