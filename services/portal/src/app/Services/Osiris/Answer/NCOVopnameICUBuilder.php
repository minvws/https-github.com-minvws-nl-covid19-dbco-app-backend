<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVopnameICUBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->hospital->isAdmitted !== YesNoUnknown::yes()) {
            return null;
        }


        if ($case->hospital->isInICU !== YesNoUnknown::yes()) {
            return null;
        }

        if ($case->hospital->admittedInICUAt === null) {
            return 'Onb';
        }

        return Utils::mapYesNoUnknown($case->hospital->isInICU);
    }
}
