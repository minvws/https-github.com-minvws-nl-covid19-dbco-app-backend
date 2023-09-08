<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy;

use App\Events\PolicyVersionCreated;
use App\Exceptions\Policy\PolicyFactMissingException;
use App\Exceptions\Policy\RiskProfileMatchNotFoundException;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Services\Policy\IndexPolicyFacts;
use App\Services\Policy\IndexPolicyGuidelineProvider;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Generator;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('calendar')]
#[Group('policy')]
#[Group('policyGuideline')]
final class IndexPolicyGuidelineProviderTest extends FeatureTestCase
{
    #[DataProvider('sourcePeriodUseCases')]
    public function testSourcePeriod(IndexPolicyFacts $facts, ?CarbonPeriod $expectedPeriod): void
    {
        /** @var IndexPolicyGuidelineProvider $policyGuidelineProvider */
        $policyGuidelineProvider = $this->app->make(IndexPolicyGuidelineProvider::class);

        if ($expectedPeriod === null) {
            $this->expectException(RiskProfileMatchNotFoundException::class);
            $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts)->calculateSourcePeriod($facts);
        } else {
            $this->assertEquals(
                $expectedPeriod,
                $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts)->calculateSourcePeriod($facts),
            );
        }
    }

    public static function sourcePeriodUseCases(): Generator
    {
        $firstSickDate = CarbonImmutable::create(2021, 5, 26);

        yield 'hasSymptoms yes' => [
            IndexPolicyFacts::create(
                YesNoUnknown::yes(),
                null,
                null,
                null,
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(CarbonImmutable::create(2021, 5, 12), CarbonImmutable::create(2021, 5, 24)),
        ];

        yield 'hasSymptoms no' => [
            IndexPolicyFacts::create(
                YesNoUnknown::no(),
                null,
                null,
                null,
            )->withDateOfTest($firstSickDate),
            CarbonPeriod::create(CarbonImmutable::create(2021, 5, 12), CarbonImmutable::create(2021, 5, 25)),
        ];

        yield 'hasSymptoms unknown' => [
            IndexPolicyFacts::create(
                YesNoUnknown::unknown(),
                null,
                null,
                null,
            )->withDateOfSymptomOnset($firstSickDate),
            null,
        ];

        yield 'without first sick date' => [
            IndexPolicyFacts::create(null, null, null, null),
            null,
        ];
    }

    #[DataProvider('contagiousPeriodUseCases')]
    public function testContagiousPeriod(
        IndexPolicyFacts $facts,
        ?CarbonPeriod $expectedPeriod,
    ): void {
        $policyGuidelineProvider = $this->app->make(IndexPolicyGuidelineProvider::class);

        if ($expectedPeriod === null) {
            $this->expectException(RiskProfileMatchNotFoundException::class);
            $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts)->calculateContagiousPeriod($facts);
        } else {
            $contagiousPeriod = $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts)->calculateContagiousPeriod($facts);
            $this->assertEquals($expectedPeriod, $contagiousPeriod);
        }
    }

    public static function contagiousPeriodUseCases(): Generator
    {
        $firstSickDate = CarbonImmutable::create(2021, 5, 27);

        yield 'without first sick date' => [
            IndexPolicyFacts::create(null, null, null, null),
            null,
        ];

        yield 'hasSymptoms yes' => [
            IndexPolicyFacts::create(
                YesNoUnknown::yes(),
                null,
                null,
                null,
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(
                CarbonImmutable::create(2021, 5, 25),
                CarbonImmutable::create(2021, 6, 1),
            ),
        ];

        yield 'hasSymptoms unknown, isHospitalAdmitted yes, reason other' => [
            IndexPolicyFacts::create(
                YesNoUnknown::unknown(),
                null,
                YesNoUnknown::yes(),
                HospitalReason::other(),
            )->withDateOfSymptomOnset($firstSickDate),
            null,
        ];

        yield 'hasSymptoms yes, isHospitalAdmitted yes, reason null' => [
            IndexPolicyFacts::create(
                YesNoUnknown::yes(),
                null,
                YesNoUnknown::yes(),
                null,
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(
                CarbonImmutable::create(2021, 5, 25),
                CarbonImmutable::create(2021, 6, 6),
            ),
        ];

        yield 'hasSymptoms unknown, isHospitalAdmitted yes, reason covid' => [
            IndexPolicyFacts::create(
                YesNoUnknown::unknown(),
                null,
                YesNoUnknown::yes(),
                HospitalReason::covid(),
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(
                CarbonImmutable::create(2021, 5, 25),
                CarbonImmutable::create(2021, 6, 6),
            ),

        ];

        yield 'hasSymptoms unknown, isHospitalAdmitted yes, reason unknown' => [
            IndexPolicyFacts::create(
                YesNoUnknown::unknown(),
                null,
                YesNoUnknown::yes(),
                HospitalReason::unknown(),
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(
                CarbonImmutable::create(2021, 5, 25),
                CarbonImmutable::create(2021, 6, 6),
            ),

        ];

        yield 'hasSymptoms yes, isUmmunocompromised yes' => [
            IndexPolicyFacts::create(
                YesNoUnknown::yes(),
                YesNoUnknown::yes(),
                YesNoUnknown::no(),
                null,
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(
                CarbonImmutable::create(2021, 5, 25),
                CarbonImmutable::create(2021, 6, 1),
            ),
        ];

        yield 'hasSymptoms yes, isImmunocompromised yes' => [
            IndexPolicyFacts::create(
                YesNoUnknown::yes(),
                YesNoUnknown::yes(),
                null,
                null,
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(
                CarbonImmutable::create(2021, 5, 25),
                CarbonImmutable::create(2021, 6, 1),
            ),
        ];

        yield 'hasSymptoms yes, isImmunocompromised no' => [
            IndexPolicyFacts::create(
                YesNoUnknown::yes(),
                YesNoUnknown::no(),
                null,
                null,
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(
                CarbonImmutable::create(2021, 5, 25),
                CarbonImmutable::create(2021, 6, 1),
            ),
        ];
    }

    public function testContactDateIsBothInSourceAndContagiousPeriod(): void
    {
        /** @var IndexPolicyGuidelineProvider $policyGuidelineProvider */
        $policyGuidelineProvider = $this->app->make(IndexPolicyGuidelineProvider::class);

        $firstSickDate = CarbonImmutable::create(2021, 5, 26);
        $facts = IndexPolicyFacts::create(
            YesNoUnknown::yes(),
            null,
            null,
            null,
        )->withDateOfSymptomOnset($firstSickDate);

        $policyGuidelineHandler = $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts);

        $contactDate = CarbonImmutable::parse($firstSickDate)->subDays(2);
        $this->assertEquals($contactDate, $policyGuidelineHandler->calculateSourcePeriod($facts)->getEndDate());
        $this->assertEquals($contactDate, $policyGuidelineHandler->calculateContagiousPeriod($facts)->getStartDate());
    }

    public function testGetPolicyGuidelineShouldThrowExceptionWhenRiskProfileWasNotMatched(): void
    {
        $this->expectException(RiskProfileMatchNotFoundException::class);

        $policyGuidelineProvider = $this->app->make(IndexPolicyGuidelineProvider::class);

        $facts = IndexPolicyFacts::create(null, null, null, null);

        $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts);
    }

    public function testGetPolicyGuidelineWhenFactsAreMissingShouldThrowException(): void
    {
        $this->expectException(PolicyFactMissingException::class);

        $policyGuidelineProvider = $this->app->make(IndexPolicyGuidelineProvider::class);

        $facts = IndexPolicyFacts::create(YesNoUnknown::yes(), null, null, null);

        $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts)->calculateContagiousPeriod($facts);
    }

    public function testGetPolicyGuidelineForGivenPolicyVersion(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::parse('-3 days'),
        ]);

        $oldPolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::old(),
            'start_date' => CarbonImmutable::parse('-10 days'),
        ]);

        $policyGuideline = PolicyGuideline::factory()->recycle($oldPolicyVersion)->create([
            'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
            'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
            'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
            'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
            'contagious_end_date_addition' => -10, //should be different from the populator default
        ]);

        RiskProfile::factory()->recycle($oldPolicyVersion, $policyGuideline)->create([
            'risk_profile_enum' => IndexRiskProfile::hasSymptoms(),
        ]);

        /** @var IndexPolicyGuidelineProvider $policyGuidelineProvider */
        $policyGuidelineProvider = $this->app->make(IndexPolicyGuidelineProvider::class);

        $facts = IndexPolicyFacts::create(YesNoUnknown::yes(), null, null, null);
        $facts = $facts->withDateOfSymptomOnset(CarbonImmutable::yesterday());

        $policyGuidelineHandler = new PolicyGuidelineHandler($policyGuideline);
        $this->assertEquals(
            $policyGuidelineHandler->calculateContagiousPeriod($facts),
            $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts, $oldPolicyVersion)->calculateContagiousPeriod($facts),
        );
    }
}
