<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Jobs\ExportCaseToOsiris;
use App\Jobs\RateLimited\RabbitMQJob;
use App\Listeners\JobResultSubscriber;
use App\Models\Metric\Job\JobResult as JobResultMetric;
use App\Repositories\Metric\MetricRepository;
use App\Services\MetricService;
use Dompdf\Exception;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Foundation\Application;
use Illuminate\Queue\Events\JobFailed as JobFailedEvent;
use Illuminate\Queue\Events\JobProcessed as JobProcessedEvent;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\NullLogger;
use Tests\Unit\UnitTestCase;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

use function json_encode;

final class JobResultSubscriberTest extends UnitTestCase
{
    public function testMeasureMetricOnJobProcessedEvent(): void
    {
        $mockQueueJob = $this->getMockBuilder(RabbitMQJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockQueueJob
            ->method('resolveName')
            ->willReturn($this->faker->word());

        $jobProcessedEvent = new JobProcessedEvent(connectionName: 'rabbitmq', job: $mockQueueJob);

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureCounter')
            ->with(new JobResultMetric($jobProcessedEvent->job->resolveName(), 'success', 'rabbitmq'));

        $jobResultSubscriber = new JobResultSubscriber(
            new MetricService(new NullLogger(), $metricRepository),
        );

        $jobResultSubscriber->handleProcessed($jobProcessedEvent);
    }

    public function testMetricShouldNotBeMeasuredOnPostponedJob(): void
    {
        $job = $this->getRabbitMQJob();

        $job->postpone($this->faker->numberBetween(1, 100));


        $jobProcessedEvent = new JobProcessedEvent(connectionName: 'rabbitmq', job: $job);

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->never())
            ->method('measureCounter');

        $jobResultSubscriber = new JobResultSubscriber(
            new MetricService(new NullLogger(), $metricRepository),
        );

        $jobResultSubscriber->handleProcessed($jobProcessedEvent);
    }

    public function testMetricShouldNotBeMeasuredOnProcessedOsirisExportJob(): void
    {
        $job = $this->createMock(JobContract::class);
        $job->expects($this->any())
            ->method('resolveName')->willReturn(ExportCaseToOsiris::class);

        $jobProcessedEvent = new JobProcessedEvent(connectionName: 'rabbitmq', job: $job);

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->never())
            ->method('measureCounter');

        $jobResultSubscriber = new JobResultSubscriber(
            new MetricService(new NullLogger(), $metricRepository),
        );

        $jobResultSubscriber->handleProcessed($jobProcessedEvent);
    }

    public function testMetricShouldNotBeMeasuredOnFailedOsirisExportJob(): void
    {
        $job = $this->createMock(JobContract::class);
        $job->expects($this->any())
            ->method('resolveName')->willReturn(ExportCaseToOsiris::class);

        $jobFailedEvent = new JobFailedEvent(connectionName: 'rabbitmq', job: $job, exception: new Exception('failed'));

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->never())
            ->method('measureCounter');

        $jobResultSubscriber = new JobResultSubscriber(
            new MetricService(new NullLogger(), $metricRepository),
        );

        $jobResultSubscriber->handleFailed($jobFailedEvent);
    }

    public function testFailedMetricShouldBeMeasuredOnFailedJob(): void
    {
        $job = $this->createMock(JobContract::class);
        $job->expects($this->any())
            ->method('resolveName')->willReturn($this->faker->word());

        $jobFailedEvent = new JobFailedEvent(connectionName: 'rabbitmq', job: $job, exception: new Exception('fail'));

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureCounter')
            ->with(new JobResultMetric($jobFailedEvent->job->resolveName(), 'failed', 'rabbitmq'));

        $jobResultSubscriber = new JobResultSubscriber(
            new MetricService(new NullLogger(), $metricRepository),
        );

        $jobResultSubscriber->handleFailed($jobFailedEvent);
    }

    public function getRabbitMQJob(): RabbitMQJob
    {
        $mockQueue = $this->createMock(RabbitMQQueue::class);
        $mockQueue
            ->expects($this->once())->method('ack');

        $mockMessage = $this->createMock(AMQPMessage::class);
        $mockMessage->method('getBody')->willReturn(json_encode(['job' => $this->faker->word()]));

        $app = $this->createMock(Application::class);
        return new RabbitMQJob($app, $mockQueue, $mockMessage, $this->faker->word(), $this->faker->word());
    }
}
