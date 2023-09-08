<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVvacmerkLtsandBuilder extends AbstractSingleValueBuilder
{
    /*
     * Anders, namelijk
     */
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->vaccination->isVaccinated !== YesNoUnknown::yes()) {
            return null;
        }

        $latestVaccineInjection = $case->vaccination->latestInjection();

        if ($latestVaccineInjection === null || $latestVaccineInjection->vaccineType === null) {
            return null;
        }

        if ($latestVaccineInjection->vaccineType === Vaccine::gsk()) {
            return Vaccine::gsk()->label;
        }

        if ($latestVaccineInjection->vaccineType === Vaccine::curevac()) {
            return Vaccine::curevac()->label;
        }

        if ($latestVaccineInjection->vaccineType === Vaccine::other()) {
            return $latestVaccineInjection->otherVaccineType;
        }

        return null;
    }
}
