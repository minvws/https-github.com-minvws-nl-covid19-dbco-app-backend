<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;

class NCOVtypeTestandBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if (
            $case->test->infectionIndicator === InfectionIndicator::labTest()
            && $case->test->labTestIndicator === LabTestIndicator::other()
        ) {
            return $case->test->otherLabTestIndicator;
        }

        return null;
    }
}
