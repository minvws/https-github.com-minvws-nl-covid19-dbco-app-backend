<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;

class NCOVHerTestBuilder extends AbstractSingleValueBuilder
{
    /*
     * Is de zelftest bevestigd met een laboratoriumtest?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->test->infectionIndicator !== InfectionIndicator::selfTest()) {
            return null;
        }

        return (string) match ($case->test->selfTestIndicator) {
            SelfTestIndicator::molecular() => SelfTestIndicator::molecular()->osirisCode,
            SelfTestIndicator::antigen() => SelfTestIndicator::antigen()->osirisCode,
            SelfTestIndicator::plannedRetest() => SelfTestIndicator::plannedRetest()->osirisCode,
            SelfTestIndicator::noRetest() => SelfTestIndicator::noRetest()->osirisCode,
            SelfTestIndicator::unknown() => SelfTestIndicator::unknown()->osirisCode,
            default => SelfTestIndicator::unknown()->osirisCode,
        };
    }
}
