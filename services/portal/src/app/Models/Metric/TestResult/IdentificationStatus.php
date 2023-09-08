<?php

declare(strict_types=1);

namespace App\Models\Metric\TestResult;

use App\Models\Metric\CounterMetric;

final class IdentificationStatus extends CounterMetric
{
    protected string $name = 'test_result_report_import:identification_status_counter';
    protected string $help = 'Counts the status of identification of an index';

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

    public static function noBsnAvailable(): self
    {
        return new self('no bsn available');
    }
}
