<?php

declare(strict_types=1);

namespace App\Models\Metric\Audit;

use App\Models\Metric\HistogramMetric;

final class AuditEventLogSize extends HistogramMetric
{
    protected string $name = 'audit_event_log_size_bytes';

    protected string $help = 'Measures the size of a log entry generated for an audit event';

    public function __construct(string $eventCode, float $sizeInBytes)
    {
        $this->buckets = [500, 1000, 2500, 5000, 6500, 7000, 8000];
        $this->labels = ['event_code' => $eventCode];
        $this->value = $sizeInBytes;
    }
}
