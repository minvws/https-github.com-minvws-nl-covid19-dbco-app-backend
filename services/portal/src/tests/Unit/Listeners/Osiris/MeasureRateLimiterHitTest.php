<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Osiris;

use App\Events\RateLimiter\RateLimiterHit as RateLimiterHitEvent;
use App\Listeners\Osiris\MeasureRateLimiterHit;
use App\Models\Metric\RateLimiter\RateLimiterHit as RateLimiterHitMetric;
use App\Repositories\Metric\MetricRepository;
use App\Services\MetricService;
use Mockery;
use Psr\Log\NullLogger;
use Tests\Unit\UnitTestCase;

use function array_key_exists;

class MeasureRateLimiterHitTest extends UnitTestCase
{
    public function testHandleInvokesMetricService(): void
    {
        $limiterName = $this->faker->word();
        $hitCount = $this->faker->numberBetween(0, 150);

        $metricRepository = Mockery::mock(MetricRepository::class);
        $metricRepository->expects('measureGauge')
            ->with(Mockery::on(static function (RateLimiterHitMetric $metric) use ($hitCount, $limiterName): bool {
                $valid = 1;
                $valid &= $metric->getValue() === (float) $hitCount;
                $valid &= array_key_exists('context', $metric->getLabels());
                $valid &= $limiterName === $metric->getLabels()['context'];

                return (bool) $valid;
            }));
        $metricService = new MetricService(new NullLogger(), $metricRepository);

        $listener = new MeasureRateLimiterHit($metricService);
        $listener->handle(new RateLimiterHitEvent($hitCount, $limiterName));
    }
}
