<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Models\Policy\PolicyVersion;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Feature\Helpers\LogHelper;

use function is_null;
use function json_decode;
use function sprintf;

#[Group('policy')]
#[Group('policyVersion')]
class ApiPolicyVersionControllerAuditTest extends FeatureTestCase
{
    public const DEFAULT_ENCODER_CONTEXT_DATE_FORMAT = 'Y-m-d\TH:i:sp';

    public function testDeletePolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this->assertDatabaseHas($policyVersion->getTable(), ['uuid' => $policyVersion->uuid]);

        $this
            ->delete(sprintf('api/admin/policy-version/%s', $policyVersion->uuid))
            ->assertNoContent();

        $action = 'destroy';
        $this->assertHasAuditInfoForAction($action, $policyVersion->uuid);
    }

    public function testCreatePolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $requestData = [
            'name' => 'My name',
            'startDate' => $this->faker
                ->dateTimeBetween('now + 1 month', '+1 year')
                ->format(self::DEFAULT_ENCODER_CONTEXT_DATE_FORMAT),
        ];

        $response = $this
            ->postJson('api/admin/policy-version', $requestData)
            ->assertCreated();

            $this->assertHasAuditInfoForAction('store', $response->json('uuid'), $requestData);
    }

    public function testUpdatePolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);

        $requestData = [
            'name' => $this->faker->word,
            'status' => PolicyVersionStatus::draft()->value,
        ];

        $this->putJson(
            sprintf('api/admin/policy-version/%s', $policyVersion->uuid),
            $requestData,
        )
            ->assertOk();

        $this->assertHasAuditInfoForAction('update', $policyVersion->uuid, $requestData);
    }

    private function assertHasAuditInfoForAction(string $action, string $uuid, ?array $properties = null): void
    {
        $testLoggerHandler = LogHelper::getMonologTestHandler();

        $this->assertTrue($testLoggerHandler->hasInfoThatContains(sprintf("ApiPolicyVersionController@%s", $action)));
        $auditLogJson = LogHelper::getAuditLogJson();

        $objectData = [
            'type' => 'policy-version',
            'identifier' => $uuid,
        ];

        if (!is_null($properties)) {
            $objectData['details'] = [
                'properties' => $properties,
            ];
        }

        $this->assertContains([$objectData], json_decode($auditLogJson, true));
    }
}
