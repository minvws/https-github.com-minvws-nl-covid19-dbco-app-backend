<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiCaseContactsTest extends FeatureTestCase
{
    public function testEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/contacts', $case->uuid));
        $response->assertStatus(200);
    }

    public function testWithInvalidPayloadsShouldFail(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/contacts', $case->uuid), [
            'shareNameWithContacts' => 'wrong value',
        ]);
        $response->assertStatus(400);
        $validationResult = $response->json('validationResult');
        $this->assertArrayHasKey('shareNameWithContacts', $validationResult['fatal']['failed']);
    }

    public function testContactsVersion1DoesHaveEstimatedCategory3ContactsProperty(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'schema_version' => 4,
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/contacts', $case->uuid), [
            'estimatedCategory3Contacts' => 3,
            'schemaVersion' => 1,
        ]);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($data['estimatedCategory3Contacts'], 3);
    }

    public function testContactsVersion2DoesHaveEstimatedCategory3ContactsProperty(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'schema_version' => 4,
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/contacts', $case->uuid), [
            'estimatedCategory3Contacts' => 3,
            'schemaVersion' => 2,
        ]);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($data['estimatedCategory3Contacts'], 3);
    }
}
