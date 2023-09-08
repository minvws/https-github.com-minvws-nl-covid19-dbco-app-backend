<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVpatZhsIndBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->hospital->isAdmitted !== YesNoUnknown::yes()) {
            return null;
        }

        return match ($case->hospital->reason) {
            HospitalReason::covid() => '1',
            HospitalReason::other() => '2',
            default => '3',
        };
    }
}
