<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV2UpTo4;
use MinVWS\DBCO\Enum\Models\BCOType;

class NCOVBcoTypeandBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if (
            !$case instanceof CovidCaseV2UpTo4 ||
            $case->extensiveContactTracing->receivesExtensiveContactTracing !== BCOType::other()
        ) {
            return null;
        }

        return $case->extensiveContactTracing->otherDescription ?: null;
    }
}
