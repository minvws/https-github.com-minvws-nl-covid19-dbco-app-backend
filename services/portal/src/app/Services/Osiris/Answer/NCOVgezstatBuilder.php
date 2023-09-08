<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\CauseOfDeath;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVgezstatBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        return match ($case->deceased->isDeceased) {
            YesNoUnknown::yes() => match ($case->deceased->cause) {
                CauseOfDeath::covid19() => '3',
                CauseOfDeath::other() => '4',
                default => '5', // deceased, reason unknown
            },
            YesNoUnknown::no() => '7',
            default => '6' // unknown
        };
    }
}
