<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;

class NCOVtypeTestBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        return match ($case->test->infectionIndicator) {
            InfectionIndicator::labTest() => match ($case->test->labTestIndicator) {
                LabTestIndicator::molecular() => '1',
                LabTestIndicator::antigen() => '2',
                LabTestIndicator::other() => '3',
                default => '4',
            },
            InfectionIndicator::selfTest() => '5',
            default => '4',
        };
    }
}
