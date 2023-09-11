<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Utils\Config;
use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Firebase\JWT\JWT;
use Illuminate\Testing\TestResponse;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Services\AuditService;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Prometheus\Counter;
use Prometheus\Histogram;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

use function config;
use function sprintf;

#[Group('heartbeat')]
final class HeartbeatControllerTest extends TestCase
{
    public function testResponseOk(): void
    {
        $response = $this->makeValidRequest();

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testAuditService(): void
    {
        $auditEvent = Mockery::mock(AuditEvent::class);

        $this->mock(AuditService::class, function (Mockery\MockInterface $mock) use ($auditEvent): void {
            $mock->expects('startEvent')
                ->andReturn($auditEvent);

            $auditEvent->expects('object')
                ->withArgs(static fn (AuditObject $arg) => 'heartbeat' === $arg->getType());

            $mock->expects('finalizeHttpEvent');
            $mock->expects('isEventExpected')
                ->andReturnTrue();
            $mock->expects('isEventRegistered')
                ->andReturnTrue();
        });

        $response = $this->makeValidRequest();

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testPrometheusMiddleware(): void
    {
        config()->set('prometheus.routes_buckets', ['foo', 'bar']);

        $histogram = Mockery::mock(Histogram::class);
        $histogram->expects('observe');

        $counter = Mockery::mock(Counter::class);
        $counter->expects('inc');

        $this->mock(PrometheusExporter::class, function (Mockery\MockInterface $mock) use ($histogram, $counter): void {
            $mock->expects('getOrRegisterHistogram')
                ->with('response_time_seconds', 'Observes response times', ['method', 'uri'], ['foo', 'bar'])
                ->andReturn($histogram);

            $mock->expects('getOrRegisterCounter')
                ->with('response_status_counter', 'Counts the response status codes', ['method', 'uri', 'status'])
                ->andReturn($counter);
        });

        $this->makeValidRequest();
    }

    public function testResponseFailsWithInvalidJwtToken(): void
    {
        $response = $this->json(
            Request::METHOD_GET,
            'api/v1/heartbeat',
            [],
            ['Authorization' => sprintf('Bearer %s', JWT::encode([], $this->faker->password, 'HS256', 'ggdghor'))],
        );

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    private function makeValidRequest(): TestResponse
    {
        $jwtToken = JWT::encode([], Config::string('services.jwt.secret'), 'HS256', 'ggdghor');

        return $this->json(
            Request::METHOD_GET,
            'api/v1/heartbeat',
            [],
            ['Authorization' => sprintf('Bearer %s', $jwtToken)],
        );
    }
}
