<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVpatZhsBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        return match ($case->hospital->isAdmitted) {
            YesNoUnknown::yes() => 'J',
            YesNoUnknown::no() => 'N',
            YesNoUnknown::unknown() => 'Onb',
            default => 'Onb',
        };
    }
}
