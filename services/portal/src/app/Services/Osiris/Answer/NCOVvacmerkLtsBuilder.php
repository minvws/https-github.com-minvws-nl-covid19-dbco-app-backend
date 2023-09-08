<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVvacmerkLtsBuilder extends AbstractSingleValueBuilder
{
    /*
     * Wat is, indien bekend, de merknaam van de laatste vaccinatie?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->vaccination->isVaccinated !== YesNoUnknown::yes()) {
            return null;
        }

        $lastVaccineInjection = $case->vaccination->latestInjection();
        return match ($lastVaccineInjection?->vaccineType) {
            Vaccine::pfizer() => '1',
            Vaccine::moderna() => '2',
            Vaccine::astrazeneca() => '3',
            Vaccine::janssen() => '4',
            Vaccine::other() => '7',
            Vaccine::gsk() => '7',
            Vaccine::curevac() => '7',
            Vaccine::novavax() => '10',
            Vaccine::unknown() => '8',
            default => '8' // unknown
        };
    }
}
