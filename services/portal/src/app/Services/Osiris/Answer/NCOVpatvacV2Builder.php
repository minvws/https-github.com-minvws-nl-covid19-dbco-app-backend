<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVpatvacV2Builder extends AbstractSingleValueBuilder
{
    private const YES_KNOWN_NUMBER_OF_INJECTIONS = '1';
    private const YES_UNKNOWN_NUMBER_OF_INJECTIONS = '2';
    private const NO = '3';
    private const UNKNOWN = '4';

    /*
     * Is de patiënt gevaccineerd tegen COVID-19?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        $vaccinationCount = $case->vaccination->vaccinationCount();

        return match ($case->vaccination->isVaccinated) {
            YesNoUnknown::no() => self::NO,
            YesNoUnknown::yes() => $vaccinationCount > 0
                ? self::YES_KNOWN_NUMBER_OF_INJECTIONS
                : self::YES_UNKNOWN_NUMBER_OF_INJECTIONS,
            default => self::UNKNOWN,
        };
    }
}
