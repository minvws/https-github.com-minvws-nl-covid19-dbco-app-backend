<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class NCOVwerkand15mberBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->job->closeContactAtJob !== YesNoUnknown::yes()) {
            return null;
        }

        return match ($case->job->professionOther) {
            ProfessionOther::kapper() => '1',
            ProfessionOther::schoonheidsspecialist() => '2',
            ProfessionOther::manicure() => '3',
            ProfessionOther::pedicure() => '4',
            ProfessionOther::rijinstructeur() => '5',
            ProfessionOther::winkelmedewerker() => '6',
            ProfessionOther::trainer() => '7',
            ProfessionOther::anders() => '8',
            default => null
        };
    }
}
