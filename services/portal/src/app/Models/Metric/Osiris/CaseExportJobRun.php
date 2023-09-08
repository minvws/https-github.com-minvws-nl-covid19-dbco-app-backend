<?php

declare(strict_types=1);

namespace App\Models\Metric\Osiris;

use App\Models\Metric\CounterMetric;

final class CaseExportJobRun extends CounterMetric
{
    protected string $name = 'osiris:case_export:job_run';
    protected string $help = 'Counts the jobs ran to export cases to Osiris';

    private function __construct(string $status)
    {
        $this->labels = ['status' => $status];
    }

    public static function success(): self
    {
        return new self('success');
    }

    public static function failed(): self
    {
        return new self('failed');
    }
}
