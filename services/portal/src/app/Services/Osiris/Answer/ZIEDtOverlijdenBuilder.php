<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class ZIEDtOverlijdenBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->deceased->isDeceased !== YesNoUnknown::yes()) {
            return null;
        }

        return Utils::formatDate($case->deceased->deceasedAt);
    }
}
