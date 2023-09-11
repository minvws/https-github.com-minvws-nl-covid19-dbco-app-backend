<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Listeners\JobQueuedListener;
use App\Models\Metric\Job\JobQueued as JobQueuedMetric;
use App\Repositories\Metric\MetricRepository;
use App\Services\MetricService;
use Illuminate\Queue\Events\JobQueued as JobQueuedEvent;
use Psr\Log\NullLogger;
use Tests\Unit\UnitTestCase;

final class JobQueuedListenerTest extends UnitTestCase
{
    public function testMeasureMetricOnJobQueuedEvent(): void
    {
        $jobQueuedEvent = new JobQueuedEvent(connectionName: 'rabbitmq', id: 'id', job: 'job');

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureCounter')
            ->with(new JobQueuedMetric($jobQueuedEvent));

        $jobQueuedListener = new JobQueuedListener(
            new MetricService(new NullLogger(), $metricRepository),
        );

        $jobQueuedListener->handle($jobQueuedEvent);
    }
}
