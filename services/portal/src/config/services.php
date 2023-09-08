<?php

declare(strict_types=1);

$mittensPseudoBsnTokensFor = env('MITTENS_PSEUDO_BSN_TOKENS_FOR');
if (!is_string($mittensPseudoBsnTokensFor)) {
    $mittensPseudoBsnTokensFor = '';
}

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
        'revokeUrl' => 'https://login.ggdghor.nl/ggdghornl/oauth2/v1/revoke',
        'tokenUrl' => 'https://login.ggdghor.nl/ggdghornl/oauth2/v1/token',
        'userUrl' => 'https://login.ggdghor.nl/ggdghornl/oauth2/v1/introspect',
        'claims' => [
            'vrRegioCode' => 'http://schemas.ggd.nl/ws/2020/07/identity/claims/vrregiocode',
            'department' => 'http://schemas.ggd.nl/ws/2020/10/identity/claims/department',
        ],
    ],

    'osiris' => [
        'queue' => [
            'name' => env('OSIRIS_JOB_QUEUE_NAME', 'osiris'),
            'connection' => env('OSIRIS_JOB_QUEUE_CONNECTION', 'rabbitmq'),
        ],
        'service_name' => 'osiris',
        'use_mock_client' => env('OSIRIS_USE_MOCK_CLIENT', false),
        'mock_client_response' => env('OSIRIS_MOCK_CLIENT_RESPONSE', 'success'),
        'cache_wsdl' => env('OSIRIS_CACHE_WSDL', 1),
        'connection_timeout' => env('OSIRIS_CONNECTION_TIMEOUT', 5),
        'timeout' => env('OSIRIS_TIMEOUT', 10),
        'base_url' => env('OSIRIS_BASE_URL'),
        'api_login_path' => env('OSIRIS_API_LOGIN_PATH', storage_path('secrets/osiris_api_login.json')),
        'mock_wsdl_path' => storage_path('wsdl/osiris_ws_vragenlijst_mock.xml'),
        'retry_from_date' => env('OSIRIS_RETRY_FROM_DATE', '2022-12-21 00:00:00'),
        'case_export_job' => [
            'queue_name' => env('OSIRIS_CASE_EXPORT_JOB_QUEUE_NAME', 'osiris'),
            'connection' => env('OSIRIS_CASE_EXPORT_JOB_CONNECTION', 'rabbitmq'),
            'backoff' => env('OSIRIS_CASE_EXPORT_JOB_BACKOFF', 300),
            'tries' => env('OSIRIS_CASE_EXPORT_JOB_TRIES', 2),
            'timeout' => env('OSIRIS_CASE_EXPORT_JOB_TIMEOUT', 30),
        ],
        'rate_strategy' => [
            'service_name' => 'osiris',
            'time_window' => env('OSIRIS_RATE_STRATEGY_TIME_WINDOW', 30),
            'failure_rate_threshold' => env('OSIRIS_RATE_STRATEGY_FAILURE_RATE_THRESHOLD', 50),
            'minimum_requests' => env('OSIRIS_RATE_STRATEGY_MINIMUM_REQUESTS', 10),
            'interval_to_half_open' => env('OSIRIS_RATE_STRATEGY_INTERVAL_TO_HALF_OPEN', 5),
        ],
        'rate_limit' => [
            'rate_limiter_key' => 'osiris-case-export',
            'max_jobs_per_minute' => env('OSIRIS_RATE_LIMIT_MAX_JOBS_PER_MINUTE', 150),
        ],
    ],

    'location' => [
        'base_uri' => env('LOCATION_BASE_URI'),
        'api_key' => env('LOCATION_API_KEY'),
    ],

    'bsn_provider' => env('BSN_PROVIDER', 'local'),

    'mittens' => [
        'client_options' => [
            'base_uri' => env('MITTENS_BASE_URI'),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'cert' => env('MITTENS_CLIENT_SSL_CERT'),
            'ssl_key' => env('MITTENS_CLIENT_SSL_KEY'),
            'service_name' => 'mittens',
            'timeout' => env('MITTENS_CLIENT_TIMEOUT', 5),
        ],
        'rate_strategy' => [
            'time_window' => env('MITTENS_RATE_STRATEGY_TIME_WINDOW', 30),
            'failure_rate_threshold' => env('MITTENS_RATE_STRATEGY_FAILURE_RATE_THRESHOLD', 50),
            'minimum_requests' => env('MITTENS_RATE_STRATEGY_MIMIMUM_REQUESTS', 10),
            'interval_to_half_open' => env('MITTENS_RATE_STRATEGY_INTERVAL_TO_HALF_OPEN', 5),
        ],
        'digid_access_tokens_path' => env('MITTENS_DIGID_ACCESS_TOKENS_PATH'),
        'pii_access_tokens_path' => env('MITTENS_PII_ACCESS_TOKENS_PATH'),
        'pseudo_bsn_tokens_for' => explode(',', $mittensPseudoBsnTokensFor),
        'max_retry_count' => env('MITTENS_API_MAX_RETRY_COUNT', 1),
    ],
];
