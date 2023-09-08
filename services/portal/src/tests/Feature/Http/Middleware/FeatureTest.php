<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function config;

class FeatureTest extends FeatureTestCase
{
    #[DataProvider('featureDataProvider')]
    public function testFeature(?bool $isEnabled, int $expectedStatusCode): void
    {
        config()->set('featureflag.catalog_enabled', $isEnabled);

        $user = $this->createUser();

        $response = $this->be($user)->get('/api/catalog');
        $response->assertStatus($expectedStatusCode);
    }

    public static function featureDataProvider(): array
    {
        return [
            'feature enabled' => [true, 200],
            'feature disabled' => [false, 403],
            'feature not set will default to enabled' => [null, 200],
        ];
    }
}
