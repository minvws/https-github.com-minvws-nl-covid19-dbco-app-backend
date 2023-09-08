<?php

declare(strict_types=1);

namespace App\Models\Metric\Osiris;

use App\Models\Metric\HistogramMetric;

use function round;

final class CaseTestresultBoundToForwardedDuration extends HistogramMetric
{
    protected string $name = 'osiris_case_testresult_bound_to_forwarded_duration';
    protected string $help = 'Duration between the creation of the sendToOsirisJob and the retrieval of the response';

    public function __construct(float $diffInSeconds)
    {
        $this->value = round($diffInSeconds, 2);
        $this->buckets = [1, 3, 5, 7, 10, 12, 15, 30, 60];
    }
}
