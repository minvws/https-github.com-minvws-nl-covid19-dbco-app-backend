<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVpatvacLtsDtBuilder extends AbstractSingleValueBuilder
{
    /*
     * Wat is de datum van de laatste vaccinatie?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->vaccination->isVaccinated !== YesNoUnknown::yes()) {
            return null;
        }

        $lastVaccineInjection = $case->vaccination->latestInjection();
        return $lastVaccineInjection === null ? null : Utils::formatDate($lastVaccineInjection->injectionDate);
    }
}
