<?php

declare(strict_types=1);

namespace App\Models\Metric\Audit;

use App\Models\Metric\CounterMetric;

final class AuditEventSpecDeviation extends CounterMetric
{
    protected string $name = 'audit_event_spec_deviation_total';
    protected string $help = 'Counts the occurrences of an audit event deviating from the specification';

    public function __construct(string $auditEventCode)
    {
        $this->labels['audit_event_code'] = $auditEventCode;
    }
}
