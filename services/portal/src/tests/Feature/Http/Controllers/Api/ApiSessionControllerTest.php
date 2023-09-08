<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function config;

class ApiSessionControllerTest extends FeatureTestCase
{
    #[DataProvider('sessionLifetimeDataProvider')]
    public function testRefresh(int $lifetime): void
    {
        config()->set('session.lifetime', $lifetime);

        $now = CarbonImmutable::createFromDate(2000, 1, 1);
        CarbonImmutable::setTestNow($now);

        $response = $this->post('/api/session-refresh');
        $response->assertCookie('InactivityTimerExpiryDate', $now->addMinutes($lifetime)->toISOString(), false);
    }

    public static function sessionLifetimeDataProvider(): array
    {
        return [
            '15 minutes' => [15],
            '60 minutes' => [60],
        ];
    }
}
