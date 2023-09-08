<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /** @inheritdoc */
    protected $except = [
        'downloadCompleteToken', // Token for the frontend to detect if a download was complete
        'InactivityTimerExpiryDate',
    ];
}
