<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

use function config;

class VerifyCsrfToken extends Middleware
{
    /** @inheritDoc */
    protected $except = [
        'osiris',
    ];

    /**
     * @inheritdoc
     */
    protected function inExceptArray($request): bool
    {
        // @codeCoverageIgnoreStart
        if (config('security.disable_csrf_verifications', false)) {
            return true;
        }

        return parent::inExceptArray($request);
        // @codeCoverageIgnoreEnd
    }
}
