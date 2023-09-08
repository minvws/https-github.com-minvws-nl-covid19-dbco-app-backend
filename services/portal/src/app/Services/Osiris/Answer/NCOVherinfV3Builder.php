<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVherinfV3Builder extends AbstractSingleValueBuilder
{
    /*
     * Heeft de patiënt eerder (> 8 weken geleden) een positieve SARS-CoV-2 test (PCR of antigeen(zelf-)test) gehad?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        return match ($case->test->isReinfection) {
            YesNoUnknown::yes() => 'J',
            YesNoUnknown::no() => 'N',
            YesNoUnknown::unknown() => 'Onb',
            default => 'Onb',
        };
    }
}
