<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Case;

use Illuminate\Support\Facades\Queue;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function sprintf;

class ApiCaseUpdateOrganisationTest extends FeatureTestCase
{
    public function testAssignArchivedCase(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        $organisation1 = $this->createOrganisation();
        $organisation2 = $this->createOrganisation();

        $user = $this->createUserForOrganisation($organisation1, roles: 'planner');
        $case = $this->createCaseWithFragments([
            'organisation_uuid' => $organisation1->uuid,
            'assigned_organisation_uuid' => $organisation1->uuid,
        ]);

        $archiveResponse = $this->be($user)
            ->put(sprintf('/api/cases/%s/archive', $case->uuid), ['note' => $this->faker->paragraph]);
        $archiveResponse->assertOk();

        $response = $this->be($user)
            ->post(sprintf('/api/case/%s/update-organisation', $case->uuid), [
                'organisation_uuid' => $organisation2->uuid,
            ]);

        $response->assertOk();
    }
}
