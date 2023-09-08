<?php

declare(strict_types=1);

namespace App\Models\Metric\TestResult;

use App\Models\Metric\CounterMetric;

final class ImportStatus extends CounterMetric
{
    protected string $name = 'test_result_report_import:status_counter';
    protected string $help = 'Counts the imported test result reports';

    private function __construct(string $status)
    {
        $this->labels = ['status' => $status];
    }

    public static function processed(): self
    {
        return new self('processed');
    }

    public static function failed(): self
    {
        return new self('failed');
    }
}
