<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV1UpTo4;
use App\Models\Versions\CovidCase\CovidCaseV5Up;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVwinsteltypeV3Builder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        assert($case instanceof CovidCaseV1UpTo4 || $case instanceof CovidCaseV5Up);

        if (
            $case->riskLocation->isLivingAtRiskLocation !== YesNoUnknown::yes()
            || $case->riskLocation->type === null
        ) {
            return null;
        }

        return match ($case->riskLocation->type) {
            RiskLocationType::nursingHome() => '107',
            RiskLocationType::disabledResidentalCar() => '108',
            RiskLocationType::ggzInstitution() => '109',
            RiskLocationType::assistedLiving() => '110',
            RiskLocationType::socialLiving() => '141',
            RiskLocationType::asylumCenter() => '153',
            default => '151', // other
        };
    }
}
