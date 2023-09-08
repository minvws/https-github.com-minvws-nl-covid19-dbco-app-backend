<?php

declare(strict_types=1);

namespace App\Services;

use function mb_strlen;
use function mb_strtoupper;
use function str_replace;
use function substr;

class CaseNumberService
{
    public const CASE_NUMBER_REGEX = '/^([0-9]{6,8}|[a-zA-Z]{2}\d[-\s]?\d{3}[-\s]?\d{3})$/';

    public static function sanitizeCaseNumber(?string $value): ?string
    {
        if ($value === null || mb_strlen($value) < 9) {
            // This must be an old HpZone number
            return $value;
        }

        // Strip down to pattern AB1234567 and add hyphens
        $stripped = str_replace([' ', '-'], '', $value);
        $firstThree = mb_strtoupper(substr($stripped, 0, 3));
        $middleThree = substr($stripped, 3, 3);
        $lastThree = substr($stripped, 6, 3);

        return "$firstThree-$middleThree-$lastThree";
    }
}
