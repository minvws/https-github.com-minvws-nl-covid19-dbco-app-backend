<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp_ggdcontact'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers to be used while
    | sending an e-mail. You will specify which one you are using for your
    | mailers below. You are free to add additional mailers as required.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses",
    |            "postmark", "log", "array"
    |
    */

    'mailers' => [
        'smtp_bcomail' => [
            'transport' => 'smtp',
            'host' => env('SMTP_BCOMAIL_HOST', 'smtp.bcomail.nl'),
            'port' => env('SMTP_BCOMAIL_PORT', 587),
            'encryption' => env('SMTP_BCOMAIL_ENCRYPTION', 'tls'),
            'username' => env('SMTP_BCOMAIL_USERNAME'),
            'password' => env('SMTP_BCOMAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp_ggdcontact' => [
            'transport' => 'smtp',
            'host' => env('SMTP_GGDCONTACT_HOST', 'smtp.ggdcontact.nl'),
            'port' => env('SMTP_GGDCONTACT_PORT', 587),
            'encryption' => env('SMTP_GGDCONTACT_ENCRYPTION', 'tls'),
            'username' => env('SMTP_GGDCONTACT_USERNAME'),
            'password' => env('SMTP_GGDCONTACT_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'zivver' => [
            'transport' => 'smtp',
            'host' => env('ZIVVER_HOST', 'smtp.zivver.com'),
            'port' => env('ZIVVER_PORT', 587),
            'encryption' => env('ZIVVER_ENCRYPTION', 'tls'),
            'username' => env('ZIVVER_USERNAME'),
            'password' => env('ZIVVER_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
            'from' => [
                'address' => env('ZIVVER_FROM_ADDRESS', 'noreply@zelfbco.nl'),
                'name' => env('ZIVVER_FROM_NAME', 'GGD'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@ggdcontact.nl'),
        'name' => env('MAIL_FROM_NAME', 'GGD Contact'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
