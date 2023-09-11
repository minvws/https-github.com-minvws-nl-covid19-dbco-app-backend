<?php

declare(strict_types=1);

$trustedHosts = env('TRUSTED_HOSTS');

return [
    'indexSalt' => env('INDEX_SALT'),
    'trustedHosts' => explode(',', is_string($trustedHosts) ? $trustedHosts : ''),
    'trusted_proxies' => env('TRUSTED_PROXIES'),
    'exportCursorJwtSecret' => env('EXPORT_CURSOR_JWT_SECRET'),
    'useFakeHSM' => env('SECURITY_MODULE_TYPE') === 'fake',
    'disable_csrf_verifications' => env('DISABLE_CSRF_VERIFICATIONS', false),
];
