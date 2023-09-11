<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Web;

use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

class AdminControllerTest extends FeatureTestCase
{
    #[DataProvider('indexPermissionsProvider')]
    public function testIndexPermissions(string $roles, int $expectedResponseStatusCode): void
    {
        $user = $this->createUser(['consented_at' => CarbonImmutable::now()], $roles);

        $this->be($user)->get('/beheren')->assertStatus($expectedResponseStatusCode);
    }

    public static function indexPermissionsProvider(): Generator
    {
        yield 'user' => ['user', 403];
        yield 'planner' => ['planner', 403];
        yield 'user,planner' => ['user,planner', 403];
        yield 'admin' => ['admin', 200];
    }

    public function testIndex(): void
    {
        ConfigHelper::enableFeatureFlag('admin_view_enabled');

        $user = $this->createUser(['consented_at' => CarbonImmutable::now()], 'admin');

        $response = $this->be($user)->get('/beheren');
        $response->assertStatus(200);
        $response->assertViewIs('admin');
    }

    public function testIndexDisabledFeatureFlag(): void
    {
        ConfigHelper::disableFeatureFlag('admin_view_enabled');

        $user = $this->createUser(['consented_at' => CarbonImmutable::now()], 'admin');

        $response = $this->be($user)->get('/beheren');
        $response->assertStatus(403);
    }
}
