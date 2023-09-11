<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\RiskProfile;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\RiskProfile;
use App\Services\Policy\IndexPolicyFacts;
use App\Services\Policy\RiskProfile\IndexRiskProfileHandlerFactory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_key_exists;

#[Group('policy')]
#[Group('riskProfile')]
class IndexRiskProfileHandlerTest extends FeatureTestCase
{
    #[DataProvider('isApplicableDataProvider')]
    public function testIsApplicable(IndexRiskProfile $riskProfileEnum, array $factsData): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $riskProfile = RiskProfile::factory()->create([
            'risk_profile_enum' => $riskProfileEnum,
        ]);

        $riskProfileHandler = IndexRiskProfileHandlerFactory::create($riskProfile);

        $facts = IndexPolicyFacts::create(
            $factsData['hasSymptoms'] ?? null,
            $factsData['isImmunoCompromised'] ?? null,
            $factsData['isHospitalAdmitted'] ?? null,
            $factsData['hospitalReason'] ?? null,
        );

        if (array_key_exists('dateOfSymptomOnset', $factsData)) {
            $facts = $facts->withDateOfSymptomOnset(CarbonImmutable::parse($factsData['dateOfSymptomOnset']));
        }

        if (array_key_exists('dateOfTest', $factsData)) {
            $facts = $facts->withDateOfTest(CarbonImmutable::parse($factsData['dateOfTest']));
        }

        $this->assertTrue($riskProfileHandler->isApplicable($facts));
    }

    public static function isApplicableDataProvider(): array
    {
        return [
            'HasSymptoms' => [
                IndexRiskProfile::hasSymptoms(),
                [
                    'hasSymptoms' => YesNoUnknown::yes(),
                    'dateOfSymptomOnset' => '2021-5-26',
                ],

            ],
            'HospitalAdmitted' => [
                IndexRiskProfile::hospitalAdmitted(),
                [
                    'hasSymptoms' => YesNoUnknown::yes(),
                    'isHospitalAdmitted' => YesNoUnknown::yes(),
                    'hospitalReason' => HospitalReason::covid(),
                    'dateOfSymptomOnset' => '2021-5-26',
                ],

            ],
            'ImmunoCompromised' => [
                IndexRiskProfile::isImmunoCompromised(),
                [
                    'hasSymptoms' => YesNoUnknown::yes(),
                    'isImmunoCompromised' => YesNoUnknown::yes(),
                    'dateOfSymptomOnset' => '2021-5-26',
                ],

            ],
            'NoSymptoms' => [
                IndexRiskProfile::noSymptoms(),
                [
                    'hasSymptoms' => YesNoUnknown::no(),
                    'dateOfTest' => '2021-5-26',
                ],
            ],
        ];
    }
}
