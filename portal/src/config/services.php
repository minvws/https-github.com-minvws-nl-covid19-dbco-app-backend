<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'identityhub' => [
        'client_id' => env('TIH_CLIENT_ID'),
        'client_secret' => env('TIH_CLIENT_SECRET'),
        'redirect' => env('TIH_REDIRECT_URL'),
        'authUrl' => 'https://login.ggdghor.nl/ggdghornl/oauth2/v1/auth',
        'tokenUrl' => 'https://login.ggdghor.nl/ggdghornl/oauth2/v1/token',
        'userUrl' => 'https://login.ggdghor.nl/ggdghornl/oauth2/v1/introspect',
        'organisationClaim' => 'http://schemas.ggd.nl/ws/2020/07/identity/claims/vrregiocode'
    ],

    'private_api' => [
        'client_options' => [
            'base_uri' => env('PRIVATE_API_BASE_URI'),
            // enable to send request/response output to stderr
            // 'debug' => fopen('php://stderr', 'a+')
        ],
        'jwt_secret' => env('PRIVATE_API_JWT_SECRET')
    ]
];
