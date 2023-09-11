<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('policyGuideline')]
class ApiPolicyGuidelineControllerTest extends FeatureTestCase
{
    public const RESPONSE_STRUCTURE = [
        'uuid',
        'policyVersionUuid',
        'policyVersionStatus',
        'name',
        'sourceStartDateReference',
        'sourceStartDateAddition',
        'sourceEndDateReference',
        'sourceEndDateAddition',
        'contagiousStartDateReference',
        'contagiousStartDateAddition',
        'contagiousEndDateReference',
        'contagiousEndDateAddition',
    ];

    public function testPolicyGuidelineList(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        PolicyGuideline::factory()->recycle($policyVersion)->count(2)->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/policy-guideline', $policyVersion->uuid))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure(['*' => self::RESPONSE_STRUCTURE])
            ->assertJsonCount(2);
    }

    public function testPolicyGuidelineListWithNonExistingPolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation([], 'admin');
        $this->be($user);

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/policy-guideline', $this->faker->uuid))
            ->assertNotFound();
    }

    public function testPolicyGuidelineListWithoutAuthentication(): void
    {
        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/policy-guideline', $this->faker->uuid))
            ->assertUnauthorized();
    }

    public function testPolicyGuidelineListWithIncorrectRoleUnauthorized(): void
    {
        $user = $this->createUserWithOrganisation([]);
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/policy-guideline', $policyVersion->uuid))
            ->assertForbidden();
    }

    public function testGetPolicyGuidelineByUuid(): void
    {
        $user = $this->createUserWithOrganisation([], 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $policyVersion->uuid, $policyGuideline->uuid))
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE)
            ->assertJson(['uuid' => $policyGuideline->uuid], false);
    }

    public function testGetPolicyGuidelineByUuidNotFound(): void
    {
        $user = $this->createUserWithOrganisation([], 'admin');
        $this->be($user);

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $this->faker->uuid, $this->faker->uuid))
            ->assertNotFound();
    }

    public function testPolicyGuidelineByUuidWithoutAuthentication(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();

        $this
            ->putJson(sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $policyVersion->uuid, $policyGuideline->uuid))
            ->assertUnauthorized();
    }

    public function testPolicyGuidelineByUuidWithIncorrectRoleUnauthorized(): void
    {
        $user = $this->createUserWithOrganisation([]);
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();

        $this
            ->putJson(sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $policyVersion->uuid, $policyGuideline->uuid))
            ->assertForbidden();
    }

    public function testUpdatePolicyGuidelineWithBareMinumum(): void
    {
        $user = $this->createUserWithOrganisation([], 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $policyGuidelineData = PolicyGuideline::factory()->make();

        $this
            ->putJson(
                sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $policyVersion->uuid, $policyGuideline->uuid),
                ['name' => $policyGuidelineData->name],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas('policy_guideline', [
            'uuid' => $policyGuideline->uuid,
            'name' => $policyGuidelineData->name,
        ]);
    }

    public function testUpdatePolicyGuidelineWithFullRequest(): void
    {
        $user = $this->createUserWithOrganisation([], 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $policyGuidelineData = PolicyGuideline::factory()->make();

        $this
            ->putJson(
                sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $policyVersion->uuid, $policyGuideline->uuid),
                [
                    'name' => $policyGuidelineData->name,
                    'sourceStartDateReference' => $policyGuidelineData->source_start_date_reference->value,
                    'sourceStartDateAddition' => $policyGuidelineData->source_start_date_addition,
                    'sourceEndDateReference' => $policyGuidelineData->source_end_date_reference->value,
                    'sourceEndDateAddition' => $policyGuidelineData->source_end_date_addition,
                    'contagiousStartDateReference' => $policyGuidelineData->contagious_start_date_reference->value,
                    'contagiousStartDateAddition' => $policyGuidelineData->contagious_start_date_addition,
                    'contagiousEndDateReference' => $policyGuidelineData->contagious_end_date_reference->value,
                    'contagiousEndDateAddition' => $policyGuidelineData->contagious_end_date_addition,
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas(
            'policy_guideline',
            [
                'uuid' => $policyGuideline->uuid,
                'name' => $policyGuidelineData->name,
                'source_start_date_reference' => $policyGuidelineData->source_start_date_reference->value,
                'source_start_date_addition' => $policyGuidelineData->source_start_date_addition,
                'source_end_date_reference' => $policyGuidelineData->source_end_date_reference->value,
                'source_end_date_addition' => $policyGuidelineData->source_end_date_addition,
                'contagious_start_date_reference' => $policyGuidelineData->contagious_start_date_reference->value,
                'contagious_start_date_addition' => $policyGuidelineData->contagious_start_date_addition,
                'contagious_end_date_reference' => $policyGuidelineData->contagious_end_date_reference->value,
                'contagious_end_date_addition' => $policyGuidelineData->contagious_end_date_addition,
            ],
        );
    }

    public function testUpdatePolicyGuidelineNotFound(): void
    {
        $user = $this->createUserWithOrganisation([], 'admin');
        $this->be($user);

        $this
            ->putJson(
                sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $this->faker->uuid, $this->faker->uuid),
                PolicyGuideline::factory()->make()->toArray(),
            )
            ->assertNotFound();
    }

    public function testUpdatePolicyGuidelineWithoutAuthentication(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();

        $this
            ->putJson(
                sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $policyVersion->uuid, $policyGuideline->uuid),
                PolicyGuideline::factory()->make()->toArray(),
            )
            ->assertUnauthorized();
    }

    public function testUpdatePolicyGuidelineWithIncorrectRoleUnauthorized(): void
    {
        $user = $this->createUserWithOrganisation([]);
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();

        $response = $this
            ->putJson(
                sprintf('/api/admin/policy-version/%s/policy-guideline/%s', $policyVersion->uuid, $policyGuideline->uuid),
                PolicyGuideline::factory()->make()->toArray(),
            );
            $response->assertForbidden();
    }
}
