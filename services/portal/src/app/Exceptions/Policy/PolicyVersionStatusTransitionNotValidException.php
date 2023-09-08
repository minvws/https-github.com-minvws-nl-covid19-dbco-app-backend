<?php

declare(strict_types=1);

namespace App\Exceptions\Policy;

use Illuminate\Validation\ValidationException;

class PolicyVersionStatusTransitionNotValidException extends ValidationException
{
    public static function create(): self
    {
        return self::withMessages([
            'status' => ['Invalid status transition.'],
        ]);
    }
}
