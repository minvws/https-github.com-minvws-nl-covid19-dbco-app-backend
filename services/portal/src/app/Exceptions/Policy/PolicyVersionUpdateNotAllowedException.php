<?php

declare(strict_types=1);

namespace App\Exceptions\Policy;

use Illuminate\Validation\ValidationException;

class PolicyVersionUpdateNotAllowedException extends ValidationException
{
    public static function create(): self
    {
        return self::withMessages([
            'policyVersion' => ['Changes are not allowed unless status is on draft.'],
        ]);
    }
}
