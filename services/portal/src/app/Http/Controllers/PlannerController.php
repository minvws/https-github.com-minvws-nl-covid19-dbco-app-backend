<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Services\AuditService;

use function view;

class PlannerController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {
    }

    #[SetAuditEventDescription('Cases opgehaald')]
    public function listCases(): View
    {
        $this->auditService->setEventExpected(false);

        return view('planneroverview');
    }
}
