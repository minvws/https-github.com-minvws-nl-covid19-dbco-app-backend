<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use MinVWS\HealthCheck\HealthChecker;

/**
 * Used for performing health checks.
 *
 * @package App\Http\Controllers
 */
class StatusController extends Controller
{
    /**
     * Ping.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function ping()
    {
        return response('PONG', Response::HTTP_OK);
    }

    /**
     * Health check.
     *
     * @param HealthChecker $healthChecker
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(HealthChecker $healthChecker)
    {
        $result = $healthChecker->performHealthChecks();
        return response()->json($result->jsonSerialize(), $result->isHealthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
