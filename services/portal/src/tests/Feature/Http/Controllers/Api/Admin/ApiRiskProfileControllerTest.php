<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function count;
use function sprintf;

#[Group('policy')]
#[Group('riskProfile')]
class ApiRiskProfileControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);
    }

    public const RESPONSE_STRUCTURE = [
        'uuid',
        'policyVersionUuid',
        'name',
        'riskProfileEnum',
        'policyGuidelineUuid',
        'isActive',
    ];

    // LIST
    public function testListRiskProfileRequiresAuthentication(): void
    {
        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile', $this->faker->uuid))
            ->assertUnauthorized();
    }

    public function testListRiskProfileRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $queryPersonType = PolicyPersonType::contact()->value;

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile?filter[person]=%s', $policyVersion->uuid, $queryPersonType))
            ->assertForbidden();
    }

    public function testListRiskProfile(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        RiskProfile::factory()->recycle($policyVersion)->count(count(IndexRiskProfile::all()))->create([
            'person_type_enum' => PolicyPersonType::index(),
            'risk_profile_enum' => fn () => $this->faker->unique()->randomElement(IndexRiskProfile::all()),
        ]);
        RiskProfile::factory()->recycle($policyVersion)->count($this->faker->numberBetween(1, count(ContactRiskProfile::all())))->create([
            'person_type_enum' => PolicyPersonType::contact(),
            'risk_profile_enum' => fn () => $this->faker->unique()->randomElement(ContactRiskProfile::all()),
        ]);
        $queryPersonType = PolicyPersonType::index()->value;

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile?filter[person]=%s', $policyVersion->uuid, $queryPersonType))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure(['*' => self::RESPONSE_STRUCTURE])
            ->assertJsonCount(count(IndexRiskProfile::allValues()));
    }

    public function testListRiskProfileWithNoFilter(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile', $policyVersion->uuid))
            ->assertBadRequest()
            ->assertJsonValidationErrors(['filter.person' => 'Query filter parameter "person" is required!']);
    }

    public function testListRiskProfileWithInvalidFilter(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $queryPersonType = $this->faker->word();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile?filter[person]=%s', $policyVersion->uuid, $queryPersonType))
            ->assertBadRequest()
            ->assertJsonValidationErrors(
                ['filter.person' => 'Query filter parameter "person" is invalid! Allowed values are: "index", "contact".'],
            );
    }

    // GET
    public function testGetRiskProfileRequiresAuthentication(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $riskProfile = RiskProfile::factory()->recycle($policyVersion)->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $policyVersion->uuid, $riskProfile->uuid))
            ->assertUnauthorized();
    }

    public function testGetRiskProfileRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $riskProfile = RiskProfile::factory()->recycle($policyVersion)->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $policyVersion->uuid, $riskProfile->uuid))
            ->assertForbidden();
    }

    public function testGetRiskProfile(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $riskProfile = RiskProfile::factory()->recycle($policyVersion)->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $policyVersion->uuid, $riskProfile->uuid))
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE)
            ->assertJson(['uuid' => $riskProfile->uuid], false);
    }

    public function testGetRiskProfileReturnsNotFound(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $policyVersion->uuid, $this->faker->uuid()))
            ->assertNotFound();
    }

    // UPDATE
    public function testUpdateRiskProfileRequiresAuthentication(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $riskProfile = RiskProfile::factory()->recycle($policyVersion)->create();

        $this
            ->putJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $policyVersion->uuid, $riskProfile->uuid), [
                'policyGuidelineUuid' => $this->faker->uuid(),
            ])
            ->assertUnauthorized();
    }

    public function testUpdateRiskProfileRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $riskProfile = RiskProfile::factory()->recycle($policyVersion)->create();

        $this
            ->putJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $policyVersion->uuid, $riskProfile->uuid), [
                'policyGuidelineUuid' => $this->faker->uuid(),
            ])
            ->assertForbidden();
    }

    public function testUpdateRiskProfile(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $riskProfile = RiskProfile::factory()->recycle($policyVersion)->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();

        $this
            ->putJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $policyVersion->uuid, $riskProfile->uuid), [
                'policyGuidelineUuid' => $policyGuideline->uuid,
            ])
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas('risk_profile', [
            'uuid' => $riskProfile->uuid,
            'policy_guideline_uuid' => $policyGuideline->uuid,
        ]);
    }

    public function testUpdateRiskProfileNotFound(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $this
            ->putJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $this->faker->uuid(), $this->faker->uuid()), [
                'policyGuidelineUuid' => $this->faker->uuid(),
            ])
            ->assertNotFound();
    }
}
