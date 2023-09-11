<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Metric\Job;

use App\Models\Metric\Job\JobQueued;
use Illuminate\Queue\Events\JobQueued as JobQueuedEvent;
use stdClass;
use Tests\Unit\UnitTestCase;

final class JobQueuedTest extends UnitTestCase
{
    public function testJobLabelEqualsJobString(): void
    {
        $job = stdClass::class;

        $jobQueued = new JobQueued(
            new JobQueuedEvent(connectionName: 'rabbitmq', id: 'id', job: $job),
        );

        $this->assertEquals($job, $jobQueued->getLabels()['job']);
    }

    public function testJobLabelSetToClassStringWhenExtractedFromObject(): void
    {
        $job = new stdClass();

        $jobQueued = new JobQueued(
            new JobQueuedEvent(connectionName: 'rabbitmq', id: 'id', job: $job),
        );

        $this->assertEquals($job::class, $jobQueued->getLabels()['job']);
    }

    public function testJobNameSetToClosureWhenExtractedFromClosure(): void
    {
        $job = static function (): string {
            return 'hi';
        };

        $jobQueued = new JobQueued(
            new JobQueuedEvent(connectionName: 'rabbitmq', id: 'id', job: $job),
        );

        $this->assertEquals('Closure', $jobQueued->getLabels()['job']);
    }

    public function testJobNameSetToUnknownWhenExtractedFromCallableArray(): void
    {
        $job = [
            new class {
                public function __invoke(): void
                {
                }
            },
            '__invoke',
        ];


        $jobQueued = new JobQueued(
            new JobQueuedEvent(connectionName: 'rabbitmq', id: 'id', job: $job),
        );

        $this->assertEquals('unknown', $jobQueued->getLabels()['job']);
    }

    public function testConnectionLabel(): void
    {
        $connection = 'rabbitmq';

        $jobQueued = new JobQueued(
            new JobQueuedEvent(connectionName: $connection, id: 'id', job: stdClass::class),
        );

        $this->assertEquals($connection, $jobQueued->getLabels()['connection']);
    }
}
