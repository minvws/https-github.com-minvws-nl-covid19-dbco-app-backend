<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Test\TestV1UpTo3;
use App\Models\Versions\CovidCase\Test\TestV4Up;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVherinfmeldnrBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->test));
        assert($case->test instanceof Test);

        if (
            $case->test->isReinfection !== YesNoUnknown::yes() ||
            $case->test->previousInfectionReported !== YesNoUnknown::yes() ||
            $case->test->previousInfectionProven !== YesNoUnknown::yes()
        ) {
            return null;
        }

        if ($case->test instanceof TestV1UpTo3) {
            return $case->test->previousInfectionHpzoneNumber;
        }

        assert($case->test instanceof TestV4Up);
        return $case->test->previousInfectionCaseNumber;
    }
}
