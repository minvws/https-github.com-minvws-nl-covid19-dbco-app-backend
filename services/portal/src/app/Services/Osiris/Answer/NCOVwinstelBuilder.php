<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV1UpTo4;
use App\Models\Versions\CovidCase\CovidCaseV5Up;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVwinstelBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        assert($case instanceof CovidCaseV1UpTo4 || $case instanceof CovidCaseV5Up);
        return match ($case->riskLocation->isLivingAtRiskLocation) {
            YesNoUnknown::yes() => 'J',
            YesNoUnknown::no() => 'N',
            default => 'Onb',
        };
    }
}
