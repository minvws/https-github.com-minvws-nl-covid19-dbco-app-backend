<?php

declare(strict_types=1);

namespace App\Services\Policy\RiskProfile;

use App\Exceptions\Policy\UnsupportedRiskProfileHandlerException;
use App\Models\Policy\RiskProfile;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use Webmozart\Assert\Assert;

class IndexRiskProfileHandlerFactory
{
    public static function create(RiskProfile $riskProfile): RiskProfileHandler
    {
        $policyGuidelineModel = $riskProfile->policyGuideline()->firstOrFail();

        $policyGuidelineHandler = new PolicyGuidelineHandler($policyGuidelineModel);
        Assert::isInstanceOf($policyGuidelineHandler, PolicyGuidelineHandler::class);

        return self::buildFromEnum($riskProfile->risk_profile_enum, $policyGuidelineHandler);
    }

    private static function buildFromEnum(?IndexRiskProfile $riskProfileEnum, PolicyGuidelineHandler $policyGuidelineHandler): RiskProfileHandler
    {
        return match ($riskProfileEnum) {
            IndexRiskProfile::hospitalAdmitted() => new HospitalAdmittedHandler($policyGuidelineHandler),
            IndexRiskProfile::hasSymptoms() => new HasSymptomsHandler($policyGuidelineHandler),
            IndexRiskProfile::isImmunoCompromised() => new IsImmunoCompromisedHandler($policyGuidelineHandler),
            IndexRiskProfile::noSymptoms() => new NoSymptomsHandler($policyGuidelineHandler),
            default => throw new UnsupportedRiskProfileHandlerException(),
        };
    }
}
