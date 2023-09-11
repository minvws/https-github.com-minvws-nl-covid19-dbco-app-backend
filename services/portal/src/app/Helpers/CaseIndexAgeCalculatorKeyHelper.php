<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\CarbonImmutable;
use DateTimeInterface;

class CaseIndexAgeCalculatorKeyHelper
{
    public static function getCalculatorKey(DateTimeInterface $dateTime): int
    {
        return CarbonImmutable::instance($dateTime)->month;
    }
}
