<?php

declare(strict_types=1);

namespace App\Listeners\Audit;

use App\Models\Metric\Audit\AuditEventLogSize;
use App\Services\MetricService;
use MinVWS\Audit\Events\AuditEventRegistered;
use MinVWS\Audit\Repositories\GgdSocAuditRepository;

use function strlen;

final readonly class MeasureAuditEventSize
{
    public function __construct(
        private MetricService $metricService,
    ) {
    }

    public function __invoke(AuditEventRegistered $event): void
    {
        if ($event->origin !== GgdSocAuditRepository::class) {
            return;
        }

        $this->metricService->measure(
            new AuditEventLogSize($event->auditEventCode, (float) strlen($event->serializedAuditEvent)),
        );
    }
}
