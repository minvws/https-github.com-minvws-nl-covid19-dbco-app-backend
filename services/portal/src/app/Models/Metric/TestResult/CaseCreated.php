<?php

declare(strict_types=1);

namespace App\Models\Metric\TestResult;

use App\Models\Metric\CounterMetric;

final class CaseCreated extends CounterMetric
{
    protected string $name = 'test_result_report_import:case_created_counter';
    protected string $help = 'Counts the total number of created cases';

    private function __construct(string $status)
    {
        $this->labels = ['status' => $status];
    }

    public static function identified(): self
    {
        return new self('identified');
    }

    public static function notIdentified(): self
    {
        return new self('not identified');
    }
}
