<?php

declare(strict_types=1);

namespace App\Models\Metric\Osiris;

use App\Models\Metric\HistogramMetric;

final class CaseCreationToForwardingDuration extends HistogramMetric
{
    protected string $name = 'osiris_case_creation_to_forwarding_duration';
    protected string $help = 'Duration between receiving a case and importing it to the database';

    public function __construct(int $diffInSeconds)
    {
        $this->value = $diffInSeconds;
        $this->buckets = [1, 3, 5, 7, 10, 12, 15, 30, 60];
    }
}
