<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Feature\Helpers\LogHelper;

use function json_decode;
use function sprintf;

#[Group('policy')]
#[Group('riskProfile')]
class ApiRiskProfileControllerAuditTest extends FeatureTestCase
{
    public function testUpdateRiskProfile(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $riskProfile = RiskProfile::factory()->recycle($policyVersion)->create();
        $riskProfileData = RiskProfile::factory()->make();

        $this
            ->putJson(sprintf('/api/admin/policy-version/%s/risk-profile/%s', $policyVersion->uuid, $riskProfile->uuid), [
                'policyGuidelineUuid' => $riskProfileData->policy_guideline_uuid,
            ])
            ->assertOk();

        $testLoggerHandler = LogHelper::getMonologTestHandler();

        $this->assertTrue($testLoggerHandler->hasInfoThatContains('ApiRiskProfileController@update'));

        $auditLogJson = LogHelper::getAuditLogJson();

        $this->assertContains([
            [
                'type' => 'risk-profile',
                'identifier' => $riskProfile->uuid,
                'details' => [
                    'properties' => [
                        'policyVersionUuid' => $policyVersion->uuid,
                        'policyGuidelineUuid' => $riskProfileData->policy_guideline_uuid,
                    ],
                ],
            ],
        ], json_decode($auditLogJson, true));
    }
}
