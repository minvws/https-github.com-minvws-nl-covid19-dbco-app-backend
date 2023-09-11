<?php

declare(strict_types=1);

use Carbon\CarbonInterface;

if (!function_exists('toDate')) {
    /**
     * @throws Exception
     */
    function toDate(
        CarbonInterface $date,
        ?string $modifierAmount = null,
        string $modifierName = 'days',
    ): string {
        if ($modifierAmount !== null) {
            $modifiedDate = $date->modify(sprintf('%s %s', $modifierAmount, $modifierName));

            if ($modifiedDate === false) {
                throw new Exception('date modification in toDate failed');
            }

            $date = $modifiedDate;
        }

        return $date->translatedFormat('l j F');
    }
}

if (!function_exists('toTime')) {
    function toTime(
        CarbonInterface $date,
    ): string {
        return $date->translatedFormat('H:i');
    }
}
