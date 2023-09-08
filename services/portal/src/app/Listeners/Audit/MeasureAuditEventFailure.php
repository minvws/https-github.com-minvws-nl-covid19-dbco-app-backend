<?php

declare(strict_types=1);

namespace App\Listeners\Audit;

use App\Models\Metric\Audit\AuditEventSchemaDeviation;
use App\Models\Metric\Audit\AuditEventSchemaMissing;
use App\Models\Metric\Audit\AuditEventSpecDeviation;
use App\Models\Metric\Audit\AuditEventSpecMissing;
use App\Services\MetricService;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;
use MinVWS\Audit\Events\AuditEventSchemaDeviates;
use MinVWS\Audit\Events\AuditEventSpecDeviates;

final class MeasureAuditEventFailure
{
    public function __construct(
        #[Config('featureflag.measure_audit_event_schema_failure_enabled')]
        private readonly bool $shouldMeasureSchemaFailure,
        #[Config('featureflag.measure_audit_event_spec_failure_enabled')]
        private readonly bool $shouldMeasureSpecFailure,
        private readonly MetricService $metricService,
    ) {
    }

    public function whenSchemaIsMissing(): void
    {
        if (!$this->shouldMeasureSchemaFailure) {
            return;
        }

        $this->metricService->measure(new AuditEventSchemaMissing());
    }

    public function whenSchemaDeviates(AuditEventSchemaDeviates $event): void
    {
        if (!$this->shouldMeasureSchemaFailure) {
            return;
        }

        $this->metricService->measure(new AuditEventSchemaDeviation($event->auditEvent->getCode()));
    }

    public function whenSpecIsMissing(): void
    {
        if (!$this->shouldMeasureSpecFailure) {
            return;
        }

        $this->metricService->measure(new AuditEventSpecMissing());
    }

    public function whenSpecDeviates(AuditEventSpecDeviates $event): void
    {
        if (!$this->shouldMeasureSpecFailure) {
            return;
        }

        $this->metricService->measure(new AuditEventSpecDeviation($event->auditEvent->getCode()));
    }
}
