<?php

declare(strict_types=1);

namespace App\Models\Metric\Osiris;

use App\Models\Metric\HistogramMetric;
use Webmozart\Assert\Assert;

use function range;

final class CaseExportJobAttempts extends HistogramMetric
{
    protected string $name = 'osiris:case_export:job_run_attempts';
    protected string $help = 'Observes the amount of job run attempts needed to export a case to Osiris';

    private function __construct(string $status, float $attempts, int $maxTries)
    {
        Assert::greaterThanEq($maxTries, 1);

        $this->value = $attempts;
        $this->labels = ['status' => $status];
        $this->buckets = range(1, $maxTries);
    }

    public static function success(float $attempts, int $maxTries): self
    {
        return new self('success', $attempts, $maxTries);
    }

    public static function failed(float $attempts, int $maxTries): self
    {
        return new self('failed', $attempts, $maxTries);
    }
}
