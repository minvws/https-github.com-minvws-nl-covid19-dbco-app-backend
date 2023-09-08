<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV1UpTo4;
use App\Models\Versions\CovidCase\CovidCaseV5Up;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVtrimzwangerBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        assert($case instanceof CovidCaseV1UpTo4 || $case instanceof CovidCaseV5Up);

        // only when pregnant and deceased
        if (
            $case->pregnancy->isPregnant !== YesNoUnknown::yes() ||
            $case->deceased->isDeceased !== YesNoUnknown::yes()
        ) {
            return null;
        }

        if (
            $case->deceased->deceasedAt === null
            ||
            (
                $case instanceof CovidCaseV1UpTo4
                && $case->pregnancy->dueDate === null
            )
        ) {
            return '9'; // unknown
        }

        if ($case instanceof CovidCaseV1UpTo4) {
            $deceasedAt = new CarbonImmutable($case->deceased->deceasedAt);
            $dueDate = new CarbonImmutable($case->pregnancy->dueDate);
            $weeksPregnant = $dueDate->subWeeks(40)->diffInWeeks($deceasedAt, false);

            if ($weeksPregnant >= 26) {
                return '3';
            }

            if ($weeksPregnant >= 13) {
                return '2';
            }
        }

        return '1';
    }
}
