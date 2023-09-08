<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Feature\Helpers\LogHelper;

use function json_decode;
use function sprintf;

#[Group('policy')]
#[Group('policyGuideline')]
class ApiPolicyGuidelineControllerAuditTest extends FeatureTestCase
{
    public function testUpdatePolicyGuideline(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $user = $this->createUserWithOrganisation(roles: 'admin');
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
            ->assertOk();
        $testLoggerHandler = LogHelper::getMonologTestHandler();

        $this->assertTrue($testLoggerHandler->hasInfoThatContains('ApiPolicyGuidelineController@update'));

        $auditLogJson = LogHelper::getAuditLogJson($testLoggerHandler);

        $this->assertContains([
            [
                'type' => 'policy-guideline',
                'identifier' => $policyGuideline->uuid,
                'details' => [
                    'properties' => [
                        'policyVersionUuid' => $policyVersion->uuid,
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
                ],
            ],
        ], json_decode($auditLogJson, true));
    }
}
