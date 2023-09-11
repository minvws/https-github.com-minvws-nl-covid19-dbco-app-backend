<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\CaseMetricsRefreshJob;
use App\Services\CaseMetrics\CaseMetricsService;
use Carbon\CarbonImmutable;
use Exception;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

class CaseMetricsRefreshJobTest extends FeatureTestCase
{
    public function testHandleWhenFeatureFlagDisabled(): void
    {
        ConfigHelper::disableFeatureFlag('case_metrics_enabled');
        $organisation = $this->createOrganisation();
        $periodEnd = CarbonImmutable::instance($this->faker->dateTimeBetween('-1 week'));

        $this->mock(CaseMetricsService::class, static function (MockInterface $mock): void {
            $mock->expects('refreshForOrganisation')->never();
        });

        $this->app->call([new CaseMetricsRefreshJob($organisation->uuid, $periodEnd), 'handle']);
    }

    public function testHandleWhenFeatureFlagEnabled(): void
    {
        ConfigHelper::enableFeatureFlag('case_metrics_enabled');
        $organisation = $this->createOrganisation();
        $periodEnd = CarbonImmutable::instance($this->faker->dateTimeBetween('-1 week'));

        $this->mock(
            CaseMetricsService::class,
            static function (MockInterface $mock) use ($organisation, $periodEnd): void {
                $mock->expects('refreshForOrganisation')
                    ->with($organisation->uuid, $periodEnd);
            },
        );

        $this->app->call([new CaseMetricsRefreshJob($organisation->uuid, $periodEnd), 'handle']);
    }

    public function testHandleWhenFeatureFlagEnabledButFails(): void
    {
        ConfigHelper::enableFeatureFlag('case_metrics_enabled');
        $organisation = $this->createOrganisation();
        $periodEnd = CarbonImmutable::instance($this->faker->dateTimeBetween('-1 week'));
        $exception = new Exception($this->faker->sentence);

        $this->mock(
            CaseMetricsService::class,
            static function (MockInterface $mock) use ($organisation, $periodEnd, $exception): void {
                $mock->expects('refreshForOrganisation')
                    ->with($organisation->uuid, $periodEnd)
                    ->andThrows($exception);
            },
        );
        $this->mock(LoggerInterface::class, static function (MockInterface $mock) use ($exception): void {
            $mock->expects('error')
                ->with('CaseMetricsRefreshJob failed', ['exception' => $exception]);
        });

        $this->app->call([new CaseMetricsRefreshJob($organisation->uuid, $periodEnd), 'handle']);
    }
}
