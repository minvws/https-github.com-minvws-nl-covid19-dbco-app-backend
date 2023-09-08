<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVNvacBuilder extends AbstractSingleValueBuilder
{
    /*
     * Hoeveel vaccinaties ontving de patiënt tegen COVID-19?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->vaccination->isVaccinated !== YesNoUnknown::yes()) {
            return null;
        }

        $vaccinationCount = $case->vaccination->vaccinationCount();

        return $vaccinationCount > 0 ? (string) $vaccinationCount : null;
    }
}
