<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use MinVWS\Audit\Services\AuditService;

use function response;

class ApiSessionController extends ApiController
{
    private AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Request that is only used to extend the current session-lifetime
     */
    public function refresh(): JsonResponse
    {
        $this->auditService->setEventExpected(false);

        return response()->json();
    }
}
