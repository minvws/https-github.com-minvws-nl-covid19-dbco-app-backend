<?php

declare(strict_types=1);

namespace App\Models\Metric\Job;

use App\Models\Metric\CounterMetric;
use Illuminate\Queue\Events\JobQueued as Event;

use function is_callable;
use function is_object;

final class JobQueued extends CounterMetric
{
    protected string $name = 'jobs_queued_total';
    protected string $help = 'Counter for jobs dispatched on a queue';

    public function __construct(Event $event)
    {
        $this->labels = [
            'job' => $this->extractJobName($event),
            'connection' => $event->connectionName,
        ];
    }

    private function extractJobName(Event $event): string
    {
        if (is_object($event->job)) {
            return $event->job::class;
        }

        if (is_callable($event->job)) {
            return 'unknown';
        }

        return $event->job;
    }
}
