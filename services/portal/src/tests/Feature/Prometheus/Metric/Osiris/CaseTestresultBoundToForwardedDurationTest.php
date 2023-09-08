<?php

declare(strict_types=1);

namespace Tests\Feature\Prometheus\Metric\Osiris;

use App\Models\Metric\Osiris\CaseTestresultBoundToForwardedDuration;
use App\Repositories\Metric\MetricRepository;
use App\Services\MetricService;
use Tests\Feature\FeatureTestCase;

use function now;
use function round;

class CaseTestresultBoundToForwardedDurationTest extends FeatureTestCase
{
    public function testItMeasuresTheDurationBetweenTheCreationOfTheSendToOsirisJobAndTheRetrievalOfTheResponse(): void
    {
        $startTime = now();
        $endTime = now()->addSecond();
        $diff = $startTime->floatDiffInSeconds($endTime);
        $metric = new CaseTestresultBoundToForwardedDuration($diff);
        $diff = round($diff, 2);
        $this->assertIsFloat($metric->getValue());
        $this->assertIsFloat($diff);

        $this->mock(MetricRepository::class, static function ($mock) use ($diff): void {
            $mock->expects('measureHistogram')->withArgs(
                static function (CaseTestresultBoundToForwardedDuration $metric) use ($diff): bool {
                    return $metric->getValue() === $diff;
                },
            );
        });

        $metricService = $this->app->make(MetricService::class);
        $metricService->measure($metric);
    }
}
