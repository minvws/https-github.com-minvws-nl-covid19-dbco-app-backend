<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Repositories\Policy\RiskProfilePopulator;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function count;

#[Group('policy')]
#[Group('riskProfile')]
final class RiskProfilePopulatorTest extends FeatureTestCase
{
    private RiskProfilePopulator $riskProfilePopulator;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->riskProfilePopulator = $this->app->make(RiskProfilePopulator::class);
    }

    public function testPopulate(): void
    {
        $policyVersion = PolicyVersion::factory()->create();

        $result = $this->riskProfilePopulator->populate($policyVersion);

        $this->assertDatabaseCount(RiskProfile::class, count(IndexRiskProfile::all()) + count(ContactRiskProfile::all()));

        $this->assertEqualsCanonicalizing(
            [
                'has_symptoms',
                'hospital_admitted',
                'is_immuno_compromised',
                'no_symptoms',
            ],
            $result->where('person_type_enum', PolicyPersonType::index())->pluck('risk_profile_enum')->map->value->toArray(),
        );

        $this->assertEqualsCanonicalizing(
            [
                'cat1_vaccinated_distance_not_possible',
                'cat3_not_vaccinated_distance_possible',
                'cat3_vaccinated_distance_possible',
                'cat1_not_vaccinated_distance_possible',
                'cat1_not_vaccinated_distance_not_possible',
                'cat2_vaccination_unknown_distance_possible',
                'cat1_vaccinated_distance_possible',
                'cat3_vaccination_unknown_distance_possible',
                'cat2_vaccinated_distance_possible',
                'cat2_vaccinated_distance_not_possible',
                'cat2_not_vaccinated_distance_not_possible',
                'cat3_vaccination_unknown_distance_not_possible',
                'cat1_vaccination_unknown_distance_not_possible',
                'cat3_not_vaccinated_distance_not_possible',
                'cat2_vaccination_unknown_distance_not_possible',
                'cat3_vaccinated_distance_not_possible',
                'cat2_not_vaccinated_distance_possible',
                'cat1_vaccination_unknown_distance_possible',
            ],
            $result->where('person_type_enum', PolicyPersonType::contact())->pluck('risk_profile_enum')->map->value->toArray(),
        );
    }

    public function testIdempotency(): void
    {
        $policyVersion = PolicyVersion::factory()->create();

        $count = count(IndexRiskProfile::all()) + count(ContactRiskProfile::all());

        $this->riskProfilePopulator->populate($policyVersion);
        $this->assertDatabaseCount(RiskProfile::class, $count);

        $this->riskProfilePopulator->populate($policyVersion);
        $this->assertDatabaseCount(RiskProfile::class, $count);
    }
}
