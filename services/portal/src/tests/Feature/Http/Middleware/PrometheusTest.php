<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Models\Metric\Http\Response;
use App\Models\Metric\Http\ResponseTimePerOrganisation;
use App\Repositories\Metric\MetricRepository;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;

use function sprintf;

class PrometheusTest extends FeatureTestCase
{
    public function testRouteMiddlewareWithHistogramEnabled(): void
    {
        $organisation = $this->createOrganisation();

        $this->spy(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Response::class);
            $mock->expects('measureHistogram')
                ->with(ResponseTimePerOrganisation::class);
        });

        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user);
        $this->be($user)
            ->get(sprintf('/api/cases/%s/fragments/general', $case->uuid));
    }

    public function testRouteMiddlewareWithNoMatchingRoute(): void
    {
        $this->spy(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')->withArgs(static function (Response $metric): bool {
                return $metric->getLabels() === [
                    'method' => 'GET',
                    'uri' => 'no/match/for/route',
                    'status' => '404',
                ];
            });
        });

        $user = $this->createUserForOrganisation($this->createOrganisation());
        $this->be($user)
            ->get('/no/match/for/route');
    }

    public function testRouteMiddlewareWithDisallowedRequestMethod(): void
    {
        $this->spy(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')->withArgs(static function (Response $metric): bool {
                return $metric->getLabels() === [
                    'method' => 'POST',
                    'uri' => 'medische-supervisie',
                    'status' => '405',
                ];
            });
        });

        $user = $this->createUserForOrganisation($this->createOrganisation(), roles: 'medical_supervisor');
        $this->be($user)
            ->post('/medische-supervisie');
    }
}
