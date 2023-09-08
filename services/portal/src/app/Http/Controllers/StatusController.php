<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use MinVWS\Audit\Services\AuditService;
use MinVWS\HealthCheck\HealthChecker;

use function response;

use const JSON_PRETTY_PRINT;

class StatusController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function ping(): Application|ResponseFactory|Response
    {
        $this->auditService->setEventExpected(false);
        return response('PONG', Response::HTTP_OK);
    }

    public function status(HealthChecker $healthChecker): JsonResponse
    {
        $this->auditService->setEventExpected(false);
        $result = $healthChecker->performHealthChecks();
        return response()->json(
            $result->jsonSerialize(),
            $result->isHealthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE,
            [],
            JSON_PRETTY_PRINT,
        );
    }
}
