<?php

declare(strict_types=1);

namespace App\Models\Metric\TestResult;

use App\Models\Metric\HistogramMetric;

final class ImportDuration extends HistogramMetric
{
    protected string $name = 'test_result_report_import:duration_seconds';
    protected string $help = 'Observes duration of importing test result reports';
    protected ?array $buckets = [0.25, 0.5, 0.75, 1.0, 1.5, 2.0, 2.5, 3.0, 4.0, 5.0, 7.5, 10.0, 15.0, 30.0, 60.0];

    public function __construct(float $value)
    {
        $this->value = $value;
    }
}
