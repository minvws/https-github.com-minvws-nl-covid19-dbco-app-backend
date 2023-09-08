<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-fragment')]
#[Group('case-fragment-contact')]
class ApiCaseContactControllerTest extends FeatureTestCase
{
    /**
     * Test index fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/contact');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/contact');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment storage.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // check storage
        $phone = '06 12345678';
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/contact', [
            'phone' => $phone,
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($phone, $data['data']['phone']);

        // check if really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/contact');
        $data = $response->json();
        $this->assertEquals($phone, $data['data']['phone']);
    }

    public function testUpdate(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/contact', [
            'phone' => '06 12345678',
            'email' => 'test@test.com',
        ]);
        $response->assertStatus(Response::HTTP_OK);

        // Allow the phone number to be emptied, only on update
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/contact', [
            'phone' => null,
        ]);
        $response->assertStatus(Response::HTTP_OK);
    }
}
