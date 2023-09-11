<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

final class ApiCasePregnancyControllerTest extends FeatureTestCase
{
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/pregnancy');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/pregnancy');
        $response->assertStatus(404);
    }

    public function testPut(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // no fields required
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/pregnancy');
        $response->assertStatus(200);

        // store value
        $response = $this->be($user)->putJson(
            '/api/cases/' . $case->uuid . '/fragments/pregnancy',
            [
                'isPregnant' => YesNoUnknown::yes(),
            ],
        );
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['isPregnant']);

        // check if the value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/pregnancy');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['isPregnant']);

        // store changed value
        $response = $this->be($user)->putJson(
            '/api/cases/' . $case->uuid . '/fragments/pregnancy',
            [
                'isPregnant' => YesNoUnknown::no(),
            ],
        );
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::no(), $data['data']['isPregnant']);

        // check if the changed value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/pregnancy');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::no(), $data['data']['isPregnant']);
    }
}
