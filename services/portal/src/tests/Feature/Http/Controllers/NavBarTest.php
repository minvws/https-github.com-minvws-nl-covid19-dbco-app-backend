<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function config;
use function sprintf;

class NavBarTest extends FeatureTestCase
{
    #[DataProvider('environmentNameDataProvider')]
    public function testEnvironmentName(string $environmentName): void
    {
        config()->set('app.env_name', $environmentName);

        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->get('/cases');
        $response->assertStatus(200);
        $response->assertSee(sprintf('environment="%s"', $environmentName), false);
    }

    public static function environmentNameDataProvider(): array
    {
        return [
            'development' => ['development'],
            'test' => ['test'],
        ];
    }
}
