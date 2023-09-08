<?php

declare(strict_types=1);

namespace App\Models\Metric\TestResult;

use App\Models\Metric\HistogramMetric;

final class ProcessingDuration extends HistogramMetric
{
    protected string $name = 'test_result_report:processing_duration';
    protected string $help = 'Counts the seconds from the time we received the ESB message until the time it has been processed';
    protected ?array $buckets = [0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5, 6.0, 7.5, 10.0, 30.0, 60.0];

    public function __construct(float $value)
    {
        $this->value = $value;
    }
}
