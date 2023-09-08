<?php

declare(strict_types=1);

namespace App\Models\Metric\Audit;

use App\Models\Metric\CounterMetric;

final class AuditEventSchemaMissing extends CounterMetric
{
    protected string $name = 'audit_event_schema_missing_total';
    protected string $help = 'Counts the occurrences of a missing AuditEvent schema definition';
}
