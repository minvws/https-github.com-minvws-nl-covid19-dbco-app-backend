<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_keys;
use function sprintf;

#[Group('case')]
class ApiHistoryControllerTest extends FeatureTestCase
{
    public function testOsirisHistoryWithHistory(): void
    {
        $user = $this->createUser([], 'user');
        $case = $this->createCaseForUser($user);
        $dbOsirisHistory = $this->createOsirisHistoryForCase($case);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/history/osiris', $case->uuid));

        $responseHistory = $response->json()[0];
        $this->assertEquals([
            "uuid",
            "osirisValidationResponse",
            "caseIsReopened",
            "status",
            "time",
        ], array_keys($responseHistory));

        $this->assertSame($dbOsirisHistory->uuid, $responseHistory['uuid']);
    }

    public function testOsirisHistoryWithoutHistory(): void
    {
        $user = $this->createUser([], 'user');
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/history/osiris', $case->uuid));
        $this->assertCount(0, $response->json());
    }

    public function testOsirisHistoryNoPermission(): void
    {
        $user = $this->createUser([], '');
        $case = $this->createCaseForUser($user);
        $this->createOsirisHistoryForCase($case);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/history/osiris', $case->uuid));
        $response->assertStatus(403);
    }
}
