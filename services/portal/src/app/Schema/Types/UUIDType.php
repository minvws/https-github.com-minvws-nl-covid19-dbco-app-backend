<?php

declare(strict_types=1);

namespace App\Schema\Types;

use Ramsey\Uuid\Uuid;

use function is_string;

class UUIDType extends StringType
{
    public function isOfType(mixed $value): bool
    {
        if (!is_string($value) || !parent::isOfType($value)) {
            return false;
        }

        return Uuid::isValid($value);
    }
}
