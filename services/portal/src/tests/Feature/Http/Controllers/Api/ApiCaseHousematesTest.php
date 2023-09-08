<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiCaseHousematesTest extends FeatureTestCase
{
    public function testEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/fragments/housemates', $case->uuid));
        $response->assertStatus(200);
    }
}
