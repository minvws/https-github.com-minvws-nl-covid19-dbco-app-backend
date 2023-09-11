<?php

declare(strict_types=1);

return [
    'jwt' => [
        'issuer' => 'Portal BE',
        'audience' => 'Portal FE',
        'secret' => env('ASSIGNMENT_JWT_SECRET'),
    ],

    'stateless' => [
        'cases' => [
            'max_uuids' => 100,
        ],
    ],

    'token_fetcher' => [
        'request_header' => [
            'header_name' => 'Assignment-Token',
        ],
    ],
];
