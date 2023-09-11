<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVopnamedatumICUBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if (
            $case->hospital->isAdmitted === YesNoUnknown::yes()
            && $case->hospital->isInICU === YesNoUnknown::yes()
        ) {
            return Utils::formatDate($case->hospital->admittedInICUAt);
        }

        return null;
    }
}
