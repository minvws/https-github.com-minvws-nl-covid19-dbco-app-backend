<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * Endpoint to monitor the connection from the ESB is still alive
 */
final class HeartbeatController extends Controller
{
    public function __construct(
        private readonly PrometheusExporter $prometheusExporter,
    ) {
    }

    #[SetAuditEventDescription('Heartbeat via API')]
    public function __invoke(Request $request, AuditEvent $auditEvent): JsonResponse
    {
        $auditEvent->object(AuditObject::create('heartbeat'));

        $this->prometheusExporter
            ->getOrRegisterCounter('heartbeat', 'Heartbeat detected', ['source'])
            ->inc(['esb']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
