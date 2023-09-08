<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('case-fragment')]
#[Group('case-fragment-risk-location')]
final class ApiCaseRiskLocationTest extends FeatureTestCase
{
    public function testEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/risk-location', $case->uuid));
        $response->assertStatus(200);
    }

    public function testWithInvalidPayloadsShouldFail(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/risk-location', $case->uuid), [
            'isLivingAtRiskLocation' => 'wrong value',
            'type' => 'wrong type',
            'otherType' => 1234, // not a string
        ]);
        $response->assertStatus(400);
        $validationResult = $response->json('validationResult');
        $this->assertArrayHasKey('isLivingAtRiskLocation', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('type', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('otherType', $validationResult['fatal']['failed']);
    }
}
