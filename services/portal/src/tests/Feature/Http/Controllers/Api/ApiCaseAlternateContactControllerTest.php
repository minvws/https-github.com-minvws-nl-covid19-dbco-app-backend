<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-fragment')]
#[Group('case-fragment-alternate-contact')]
class ApiCaseAlternateContactControllerTest extends FeatureTestCase
{
    /**
     * Test index fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/alternate-contact');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/alternate-contact');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment storage.
     */
    public function testPut(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // no fields required
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/alternate-contact');
        $response->assertStatus(200);

        // store value
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/alternate-contact', [
            'hasAlternateContact' => YesNoUnknown::no(),
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::no(), $data['data']['hasAlternateContact']);

        // check if the value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/alternate-contact');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::no(), $data['data']['hasAlternateContact']);

        // store changed value
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/alternate-contact', [
            'hasAlternateContact' => YesNoUnknown::yes(),
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['hasAlternateContact']);

        // check if the changed value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/alternate-contact');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['hasAlternateContact']);
    }

    public function testHasNoAlternateContactShouldNotHaveWarnings(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // store value
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/alternate-contact', [
            'hasAlternateContact' => YesNoUnknown::no(),
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotContains('validationResult', $data);
    }

    public function testHasAlternateContactShouldHaveWarnings(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // store value
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/alternate-contact', [
            'hasAlternateContact' => YesNoUnknown::yes(),
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('validationResult', $data);
        $this->assertEquals(['Required' => []], $data['validationResult']['warning']['failed']['firstname']);
        $this->assertEquals(
            ['RequiredWithout' => ['email']],
            $data['validationResult']['warning']['failed']['phone'],
        );
    }
}
