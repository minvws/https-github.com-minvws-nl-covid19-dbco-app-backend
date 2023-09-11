<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVherinfV2Builder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->test));
        assert($case->test instanceof Test);

        if ($case->test->isReinfection === null) {
            return null;
        }

        if ($case->test->isReinfection === YesNoUnknown::no()) {
            return '3';
        }

        if ($case->test->isReinfection === YesNoUnknown::unknown()) {
            return '4';
        }

        if (
            $case->test->previousInfectionReported === YesNoUnknown::yes() &&
            $case->test->previousInfectionProven === YesNoUnknown::yes()
        ) {
            return '1';
        }

        if ($case->test->previousInfectionProven === YesNoUnknown::yes()) {
            return '5';
        }

        if ($case->test->previousInfectionReported === YesNoUnknown::yes()) {
            return '6';
        }

        return '4';
    }
}
