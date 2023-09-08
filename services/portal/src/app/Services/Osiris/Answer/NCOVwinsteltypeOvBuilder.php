<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV1UpTo4;
use App\Models\Versions\CovidCase\CovidCaseV5Up;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVwinsteltypeOvBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        assert($case instanceof CovidCaseV1UpTo4 || $case instanceof CovidCaseV5Up);

        if ($case->riskLocation->isLivingAtRiskLocation !== YesNoUnknown::yes()) {
            return null;
        }

        if ($case->riskLocation->type !== RiskLocationType::other()) {
            return null;
        }

        return $case->riskLocation->otherType;
    }
}
