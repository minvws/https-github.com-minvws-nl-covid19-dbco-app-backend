<?php

declare(strict_types=1);

namespace App\Models\Metric\TestResult;

use App\Models\Metric\CounterMetric;

final class TestResultToCovidCaseAssignment extends CounterMetric
{
    protected string $name = 'test_result_report_import:test_result_to_covid_case_assignment_counter';
    protected string $help = 'Counts the total number test results assigned to an existing covid case';

    private function __construct(string $status)
    {
        $this->labels = ['status' => $status];
    }

    public static function newCase(): self
    {
        return new self('new case');
    }

    public static function existingCase(): self
    {
        return new self('existing case');
    }
}
