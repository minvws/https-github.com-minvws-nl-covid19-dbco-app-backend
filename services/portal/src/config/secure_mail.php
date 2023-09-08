<?php

declare(strict_types=1);

return [
    /*
     * Options: "v1", "v2"
     */
    'default' => env('SECURE_MAIL_CLIENT_VERSION', 'v1'),

    'v1' => [
        'base_url' => env('SECURE_MAIL_BASE_URL'),
        'jwt_secret' => env('SECURE_MAIL_JWT_SECRET'),
    ],

    'v2' => [
        'base_url' => env('SECURE_MAIL_V2_BASE_URL'),
        'api_token' => env('SECURE_MAIL_V2_API_TOKEN'),
    ],
];
