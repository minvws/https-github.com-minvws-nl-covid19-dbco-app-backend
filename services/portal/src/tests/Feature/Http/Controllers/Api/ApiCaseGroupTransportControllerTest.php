<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-fragment')]
#[Group('case-fragment-group-transport')]
class ApiCaseGroupTransportControllerTest extends FeatureTestCase
{
    /**
     * Test index fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/group-transport');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/group-transport');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment storage.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // no required fields
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/group-transport', []);
        $response->assertStatus(200);

        // check storage
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/group-transport', [
            'withReservedSeats' => YesNoUnknown::yes(),
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['withReservedSeats']);

        // check if really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/group-transport');
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['withReservedSeats']);
    }
}
