<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV2UpTo4;
use App\Models\Versions\CovidCase\CovidCaseV5Up;
use MinVWS\DBCO\Enum\Models\BCOType;

class NCOVContactInv3Builder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if (
            (
                !$case instanceof CovidCaseV2UpTo4
                && !$case instanceof CovidCaseV5Up
            )
            || $case->extensiveContactTracing->receivesExtensiveContactTracing !== BCOType::extensive()
        ) {
            return null;
        }

        $contacts = Utils::getContactsAndSources($case);
        return $contacts->isNotEmpty() ? '1' : '5'; // started/completed (1) : unknown (5)
    }
}
