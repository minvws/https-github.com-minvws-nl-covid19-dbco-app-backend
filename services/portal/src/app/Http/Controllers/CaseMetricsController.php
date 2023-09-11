<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Services\AuditService;

use function view;

class CaseMetricsController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {
    }

    #[SetAuditEventDescription('Bekijk stuurinformatie')]
    public function listCaseMetrics(): View
    {
        $this->auditService->setEventExpected(false);

        return view('case-metrics');
    }
}
