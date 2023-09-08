<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('case-fragment')]
#[Group('case-fragment-recent-birth')]
final class ApiCaseRecentBirthV1UpTo1Test extends FeatureTestCase
{
    public function testEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 4]);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/recent-birth', $case->uuid));
        $response->assertStatus(200);
    }

    public function testWithInvalidPayloadsShouldFail(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 4]);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/recent-birth', $case->uuid), [
            'hasRecentlyGivenBirth' => 'wrong value',
            'birthDate' => 'not-a-valid-date',
            'birthRemarks' => 1234, // not a string
        ]);
        $response->assertStatus(400);
        $validationResult = $response->json('validationResult');
        $this->assertArrayHasKey('hasRecentlyGivenBirth', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('birthDate', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('birthRemarks', $validationResult['fatal']['failed']);
    }
}
