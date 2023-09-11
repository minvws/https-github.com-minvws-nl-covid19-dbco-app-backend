<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVDtbekvacLtsBuilder extends AbstractSingleValueBuilder
{
    private const YES = '1';
    private const NO = '2';

    /*
     * Is de datum van laatste vaccinatie bekend?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->vaccination->isVaccinated !== YesNoUnknown::yes()) {
            return null;
        }

        if ($case->vaccination->latestInjection()?->injectionDate === null) {
            return self::NO;
        }

        return self::YES;
    }
}
