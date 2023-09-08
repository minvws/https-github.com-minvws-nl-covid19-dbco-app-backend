<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Web;

use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class CallcenterControllerTest extends FeatureTestCase
{
    #[DataProvider('searchPermissionsProvider')]
    public function testCallcenterSearchPermissions(string $roles, int $expectedResponseStatusCode): void
    {
        $user = $this->createUser(['consented_at' => CarbonImmutable::now()], $roles);

        $this->be($user)->get('/dossierzoeken')->assertStatus($expectedResponseStatusCode);
    }

    public static function searchPermissionsProvider(): Generator
    {
        yield 'callcenter' => ['callcenter', 200];
        yield 'clusterspecialist' => ['clusterspecialist', 200];
        yield 'user' => ['user', 403];
        yield 'medical supervisor' => ['medical_supervisor', 403];
    }
}
