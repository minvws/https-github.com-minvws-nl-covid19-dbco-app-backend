<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * Class ComplianceControllerTest
 */
#[Group('compliance')]
class ComplianceControllerTest extends FeatureTestCase
{
    public function testListAccessRequests(): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], 'compliance');

        $response = $this->be($user)->get('compliance');
        $response->assertStatus(200);
    }

    public function testViewSearchResults(): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], 'compliance,user');

        $response = $this->be($user)->get('compliance/search');
        $response->assertStatus(200);
    }
}
