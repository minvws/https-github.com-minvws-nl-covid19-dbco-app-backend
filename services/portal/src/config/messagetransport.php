<?php

declare(strict_types=1);

return [
    /*
     * Options: "secure_mail", "smtp", "zivver"
     */
    'secure' => env('MESSAGE_TRANSPORT_SECURE', 'secure_mail'),
    'insecure' => env('MESSAGE_TRANSPORT_INSECURE', 'smtp'),

    // specific transport configs
    'smtp' => [
        'mailer' => env('MESSAGE_TRANSPORT_SMTP', 'smtp_ggdcontact'),
    ],
    'zivver' => [
        'mailer' => 'zivver',
    ],
];
