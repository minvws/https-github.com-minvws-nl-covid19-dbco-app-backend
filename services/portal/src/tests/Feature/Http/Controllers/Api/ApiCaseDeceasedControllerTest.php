<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-fragment')]
#[Group('case-fragment-deceased')]
class ApiCaseDeceasedControllerTest extends FeatureTestCase
{
    /**
     * Test index fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/deceased');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/deceased');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment retrieval.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // no fields required
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/deceased', []);
        $response->assertStatus(200);

        // store data
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/deceased', ['isDeceased' => 'no']);
        $response->assertStatus(200);

        // check if the value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/deceased');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('no', $data['data']['isDeceased']);
    }
}
