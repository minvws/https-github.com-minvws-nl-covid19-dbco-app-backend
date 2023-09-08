<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Services\RiskProfileService;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('riskProfile')]
class RiskProfileServiceTest extends FeatureTestCase
{
    private RiskProfileService $riskProfileService;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->riskProfileService = $this->app->make(RiskProfileService::class);
    }

    public function testGetRiskProfiles(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        [$riskProfile1, $riskProfile2] = RiskProfile::factory()
            ->recycle($policyVersion)
            ->count(2)
            ->create([
                'person_type_enum' => PolicyPersonType::index(),
                'risk_profile_enum' => fn () => $this->faker->unique()->randomElement(IndexRiskProfile::all()),
            ])
            ->all();
        $policyPersonType = PolicyPersonType::index();

        $riskProfiles = $this->riskProfileService->getRiskProfilesByPolicyVersion($policyVersion, $policyPersonType);

        $this->assertCount(2, $riskProfiles);
        $this->assertDatabaseCount(RiskProfile::class, 2);
        $this->assertDatabaseHas(RiskProfile::class, ['uuid' => $riskProfile1->uuid]);
        $this->assertDatabaseHas(RiskProfile::class, ['uuid' => $riskProfile2->uuid]);
    }

    public function testGetRiskProfilesInApplianceOrder(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $firstRiskProfile = RiskProfile::factory()
            ->recycle($policyVersion)
            ->sequence(
                [
                    'is_active' => true,
                    'sort_order' => 30,
                ],
                [
                    'is_active' => false,
                    'sort_order' => 20,
                ],
                [
                    'is_active' => true,
                    'sort_order' => 10,
                ],
            )
            ->count(3)
            ->create([
                'person_type_enum' => PolicyPersonType::index(),
                'risk_profile_enum' => fn () => $this->faker->unique()->randomElement(IndexRiskProfile::all()),
            ])
            ->last();

        $riskProfiles = $this->riskProfileService->getActiveRiskProfilesInApplianceOrderByPolicyVersion($policyVersion);

        $this->assertEquals($firstRiskProfile->uuid, $riskProfiles->first()->uuid);
    }

    public function testGetActiveRiskProfiles(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $inactiveRiskProfile = RiskProfile::factory()
            ->recycle($policyVersion)
            ->state([
                'is_active' => false,
                'risk_profile_enum' => fn () => $this->faker->unique()->randomElement(IndexRiskProfile::all()),
                'sort_order' => 10,
            ])
            ->create();

        $riskProfiles = $this->riskProfileService->getActiveRiskProfilesInApplianceOrderByPolicyVersion($policyVersion);

        $this->assertFalse($riskProfiles->has($inactiveRiskProfile->uuid));
    }
}
