<?php

declare(strict_types=1);

namespace App\Services\Bsn;

use App\Models\CovidCase\IndexAddress;
use App\Models\Task\TaskAddress;
use DateTimeInterface;

class PseudoBsnLookupInputValidator
{
    public static function isValid(?DateTimeInterface $dateOfBirth, IndexAddress|TaskAddress|null $address): bool
    {
        return $dateOfBirth !== null &&
            $address !== null &&
            $address->postalCode !== null &&
            $address->houseNumber !== null;
    }
}
