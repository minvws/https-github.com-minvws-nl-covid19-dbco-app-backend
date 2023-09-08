<?php

declare(strict_types=1);

return [
    'jwt' => [
        'secret' => env('GATEWAY_JWT_SECRET'),
    ],
    'encryption' => [
        'public_key' => env('ENCRYPTION_PUBLIC_KEY'),
    ],
];
