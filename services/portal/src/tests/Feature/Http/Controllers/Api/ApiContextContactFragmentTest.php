<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Http\Response;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('context-fragment')]
#[Group('context-fragment-contact')]
class ApiContextContactFragmentTest extends FeatureTestCase
{
    /**
     * Test index fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $response = $this->be($user)->get('/api/contexts/' . $context->uuid . '/fragments/contact');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/contexts/nonexisting/fragments/contact');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment storage.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        // check storage
        $firstname = 'Bruce';
        $lastname = 'Wayne';
        $phone = '06 12345678';
        $isInformed = true;
        $notificationConsent = YesNoUnknown::yes();
        $notificationNamedConsent = true;

        $response = $this->be($user)->putJson('/api/contexts/' . $context->uuid . '/fragments/contact', [
            'firstname' => 'Bruce',
            'lastname' => 'Wayne',
            'phone' => $phone,
            'isInformed' => $isInformed,
            'notificationConsent' => $notificationConsent,
            'notificationNamedConsent' => $notificationNamedConsent,
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($firstname, $data['data']['firstname']);
        $this->assertEquals($lastname, $data['data']['lastname']);
        $this->assertEquals($phone, $data['data']['phone']);
        $this->assertEquals($isInformed, $data['data']['isInformed']);
        $this->assertEquals($notificationConsent, $data['data']['notificationConsent']);
        $this->assertEquals($notificationNamedConsent, $data['data']['notificationNamedConsent']);

        $context->refresh();
        $this->assertEquals($firstname, $context->contact->firstname);
        $this->assertEquals($lastname, $context->contact->lastname);
        $this->assertEquals($phone, $context->contact->phone);
        $this->assertEquals($isInformed, $context->contact->isInformed);
        $this->assertEquals($notificationConsent, $context->contact->notificationConsent);
        $this->assertEquals($notificationNamedConsent, $context->contact->notificationNamedConsent);

        // check if really stored
        $response = $this->be($user)->get('/api/contexts/' . $context->uuid . '/fragments/contact');
        $data = $response->json();
        $this->assertEquals($firstname, $data['data']['firstname']);
        $this->assertEquals($phone, $data['data']['phone']);
        $this->assertEquals($isInformed, (bool) $data['data']['isInformed']);
    }

    public function testUpdate(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $response = $this->be($user)->putJson('/api/contexts/' . $context->uuid . '/fragments/contact', [
            'phone' => '06 12345678',
            'email' => 'test@test.com',
        ]);
        $response->assertStatus(Response::HTTP_OK);

        // Allow the phone number to be emptied, only on update
        $response = $this->be($user)->putJson('/api/contexts/' . $context->uuid . '/fragments/contact', [
            'phone' => null,
        ]);
        $response->assertStatus(Response::HTTP_OK);
    }
}
