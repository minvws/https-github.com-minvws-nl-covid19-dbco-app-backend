<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use MinVWS\Audit\Services\AuditService;

use function view;

/**
 * @codeCoverageIgnore
 */
class PlaygroundController extends Controller
{
    private AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function viewPlayground(): View
    {
        $this->auditService->setEventExpected(false);

        return view('playground');
    }
}
