<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use MinVWS\Audit\Services\AuditService;

use function view;

class ComplianceController extends Controller
{
    private AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function listAccessRequests(): View
    {
        $this->auditService->setEventExpected(false);

        return view('complianceoverview');
    }

    public function viewSearchResults(): View
    {
        $this->auditService->setEventExpected(false);

        return view('compliance_searchresults');
    }
}
