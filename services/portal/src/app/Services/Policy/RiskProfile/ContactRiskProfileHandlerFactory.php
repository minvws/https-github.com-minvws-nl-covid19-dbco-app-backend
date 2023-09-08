<?php

declare(strict_types=1);

namespace App\Services\Policy\RiskProfile;

use App\Exceptions\Policy\UnsupportedRiskProfileHandlerException;
use App\Models\Policy\RiskProfile;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile as ContactRiskProfileEnum;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Webmozart\Assert\Assert;

class ContactRiskProfileHandlerFactory
{
    public static function create(RiskProfile $riskProfile): ContactRiskProfileHandler
    {
        $contactPolicyGuidelineModel = $riskProfile
            ->policyGuideline()
            ->firstOrFail();

        $policyGuidelineHandler = new PolicyGuidelineHandler($contactPolicyGuidelineModel);
        Assert::isInstanceOf($policyGuidelineHandler, PolicyGuidelineHandler::class);

        return self::buildFromEnum($riskProfile->risk_profile_enum, $policyGuidelineHandler);
    }

    private static function buildFromEnum(?ContactRiskProfileEnum $contactRiskProfileEnum, PolicyGuidelineHandler $guidelineHandler): ContactRiskProfileHandler
    {
        return match ($contactRiskProfileEnum) {
            ContactRiskProfileEnum::cat1VaccinatedDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat1()],
                YesNoUnknown::yes(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat1VaccinatedDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat1()],
                YesNoUnknown::yes(),
                YesNoUnknown::no(),
            ),
            ContactRiskProfileEnum::cat1NotVaccinatedDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat1()],
                YesNoUnknown::no(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat1NotVaccinatedDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat1()],
                YesNoUnknown::no(),
                YesNoUnknown::no(),
            ),
            ContactRiskProfileEnum::cat1VaccinationUnknownDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat1()],
                YesNoUnknown::unknown(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat1VaccinationUnknownDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat1()],
                YesNoUnknown::unknown(),
                YesNoUnknown::no(),
            ),
            ContactRiskProfileEnum::cat2VaccinatedDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat2a(), ContactCategory::cat2b()],
                YesNoUnknown::yes(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat2VaccinatedDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat2a(), ContactCategory::cat2b()],
                YesNoUnknown::yes(),
                YesNoUnknown::no(),
            ),
            ContactRiskProfileEnum::cat2NotVaccinatedDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat2a(), ContactCategory::cat2b()],
                YesNoUnknown::no(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat2NotVaccinatedDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat2a(), ContactCategory::cat2b()],
                YesNoUnknown::no(),
                YesNoUnknown::no(),
            ),
            ContactRiskProfileEnum::cat2VaccinationUnknownDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat2a(), ContactCategory::cat2b()],
                YesNoUnknown::unknown(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat2VaccinationUnknownDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat2a(), ContactCategory::cat2b()],
                YesNoUnknown::unknown(),
                YesNoUnknown::no(),
            ),
            ContactRiskProfileEnum::cat3VaccinatedDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat3a(), ContactCategory::cat3b()],
                YesNoUnknown::yes(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat3VaccinatedDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat3a(), ContactCategory::cat3b()],
                YesNoUnknown::yes(),
                YesNoUnknown::no(),
            ),
            ContactRiskProfileEnum::cat3NotVaccinatedDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat3a(), ContactCategory::cat3b()],
                YesNoUnknown::no(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat3NotVaccinatedDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat3a(), ContactCategory::cat3b()],
                YesNoUnknown::no(),
                YesNoUnknown::no(),
            ),
            ContactRiskProfileEnum::cat3VaccinationUnknownDistancePossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat3a(), ContactCategory::cat3b()],
                YesNoUnknown::unknown(),
                YesNoUnknown::yes(),
            ),
            ContactRiskProfileEnum::cat3VaccinationUnknownDistanceNotPossible() => new ContactRiskProfileHandler(
                $guidelineHandler,
                [ContactCategory::cat3a(), ContactCategory::cat3b()],
                YesNoUnknown::unknown(),
                YesNoUnknown::no(),
            ),
            default => throw new UnsupportedRiskProfileHandlerException(),
        };
    }
}
