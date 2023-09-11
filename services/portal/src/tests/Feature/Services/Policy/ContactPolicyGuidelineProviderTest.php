<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy;

use App\Events\PolicyVersionCreated;
use App\Exceptions\Policy\PolicyFactMissingException;
use App\Exceptions\Policy\RiskProfileMatchNotFoundException;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Services\Policy\ContactPolicyFacts;
use App\Services\Policy\ContactPolicyGuidelineProvider;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Generator;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('calendar')]
#[Group('policy')]
#[Group('contactPolicyGuideline')]
final class ContactPolicyGuidelineProviderTest extends FeatureTestCase
{
    #[DataProvider('sourcePeriodUseCases')]
    public function testSourcePeriod(ContactPolicyFacts $facts, ?CarbonPeriod $expectedPeriod): void
    {
        /** @var ContactPolicyGuidelineProvider $policyGuidelineProvider */
        $policyGuidelineProvider = $this->app->make(ContactPolicyGuidelineProvider::class);

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

        yield 'Category 1 - Vacinated | Distance Possible' => [
            ContactPolicyFacts::create(
                ContactCategory::cat1(),
                YesNoUnknown::yes(),
                YesNoUnknown::yes(),
            )->withDateOfSymptomOnset($firstSickDate),
            CarbonPeriod::create(CarbonImmutable::create(2021, 5, 12), CarbonImmutable::create(2021, 5, 24)),
        ];
    }

    public function testContactDateIsBothInSourceAndContagiousPeriod(): void
    {
        /** @var ContactPolicyGuidelineProvider $policyGuidelineProvider */
        $policyGuidelineProvider = $this->app->make(ContactPolicyGuidelineProvider::class);

        $firstSickDate = CarbonImmutable::create(2021, 5, 26);
        $facts = ContactPolicyFacts::create(
            ContactCategory::cat1(),
            YesNoUnknown::yes(),
            YesNoUnknown::yes(),
        )->withDateOfSymptomOnset($firstSickDate);

        $policyGuidelineHandler = $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts);

        $contactDate = CarbonImmutable::parse($firstSickDate)->subDays(2);
        $this->assertEquals($contactDate, $policyGuidelineHandler->calculateSourcePeriod($facts)->getEndDate());
        $this->assertEquals($contactDate, $policyGuidelineHandler->calculateContagiousPeriod($facts)->getStartDate());
    }

    public function testGetPolicyGuidelineShouldThrowExceptionWhenRiskProfileWasNotMatched(): void
    {
        $this->expectException(RiskProfileMatchNotFoundException::class);

        /** @var ContactPolicyGuidelineProvider $policyGuidelineProvider */
        $policyGuidelineProvider = $this->app->make(ContactPolicyGuidelineProvider::class);

        $facts = ContactPolicyFacts::create(ContactCategory::cat1(), null, null);

        $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts);
    }

    public function testGetPolicyGuidelineWhenFactsAreMissingShouldThrowException(): void
    {
        $this->expectException(PolicyFactMissingException::class);

        /** @var ContactPolicyGuidelineProvider $policyGuidelineProvider */
        $policyGuidelineProvider = $this->app->make(ContactPolicyGuidelineProvider::class);

        $facts = ContactPolicyFacts::create(ContactCategory::cat1(), null, YesNoUnknown::no());

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
            'person_type' => PolicyPersonType::contact(),
        ]);

        RiskProfile::factory()->recycle($oldPolicyVersion, $policyGuideline)->create([
            'risk_profile_enum' => ContactRiskProfile::cat1VaccinatedDistancePossible(),
        ]);

        /** @var ContactPolicyFacts $policyGuidelineProvider */
        $policyGuidelineProvider = $this->app->make(ContactPolicyGuidelineProvider::class);

        $facts = ContactPolicyFacts::create(ContactCategory::cat1(), YesNoUnknown::yes(), YesNoUnknown::yes());
        $facts = $facts->withDateOfSymptomOnset(CarbonImmutable::yesterday());

        $policyGuidelineHandler = new PolicyGuidelineHandler($policyGuideline);
        $this->assertEquals(
            $policyGuidelineHandler->calculateContagiousPeriod($facts),
            $policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts, $oldPolicyVersion)->calculateContagiousPeriod($facts),
        );
    }
}
