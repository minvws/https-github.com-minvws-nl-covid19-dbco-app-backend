<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Http\Response;
use MinVWS\HealthCheck\HealthChecker;
use MinVWS\HealthCheck\Models\HealthCheckResult;
use MinVWS\HealthCheck\Models\HealthCheckResultList;
use Mockery;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;

class StatusControllerTest extends FeatureTestCase
{
    public function testPingAlwaysReturnsOk(): void
    {
        $response = $this->get('/ping');
        $response->assertOk();
    }

    public function testStatusReturnsOkWhenHealthCheckWorks(): void
    {
        $successCheckResult = new HealthCheckResultList();
        $this->instance(
            HealthChecker::class,
            Mockery::mock(HealthChecker::class, static function (MockInterface $mock) use ($successCheckResult): void {
                $mock->expects('performHealthChecks')->andReturn($successCheckResult);
            }),
        );
        $response = $this->get('/status');
        $response->assertOk();
    }

    public function testStatusReturnsUnavailableWhenHealthCheckFails(): void
    {
        $failedCheckResult = new HealthCheckResultList();
        $failedCheckResult->addHealthCheckResult($this->faker->word(), new HealthCheckResult(false));
        $this->instance(
            HealthChecker::class,
            Mockery::mock(HealthChecker::class, static function (MockInterface $mock) use ($failedCheckResult): void {
                $mock->expects('performHealthChecks')->andReturn($failedCheckResult);
            }),
        );
        $response = $this->get('/status');
        $response->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
