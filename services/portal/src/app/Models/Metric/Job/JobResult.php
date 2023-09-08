<?php

declare(strict_types=1);

namespace App\Models\Metric\Job;

use App\Models\Metric\CounterMetric;

final class JobResult extends CounterMetric
{
    protected string $name = 'job_results_total';
    protected string $help = 'Counter for results of jobs';

    public function __construct(string $jobFullyQualifiedClassName, string $status, string $connection)
    {
        $this->labels = [
            'job' => $jobFullyQualifiedClassName,
            'status' => $status,
            'connection' => $connection,
        ];
    }

    public static function success(string $jobFullyQualifiedClassName, string $connection): self
    {
        return new self($jobFullyQualifiedClassName, 'success', $connection);
    }

    public static function failed(string $jobFullyQualifiedClassName, string $connection): self
    {
        return new self($jobFullyQualifiedClassName, 'failed', $connection);
    }
}
