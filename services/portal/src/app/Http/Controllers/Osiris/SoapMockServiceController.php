<?php

declare(strict_types=1);

namespace App\Http\Controllers\Osiris;

use App\Services\Osiris\SoapMockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MinVWS\Audit\Services\AuditService;

final class SoapMockServiceController
{
    public function __construct(
        private readonly AuditService $auditService,
        private readonly SoapMockService $soapMockService,
    ) {
    }

    public function getWsdl(): Response
    {
        $this->auditService->setEventExpected(false);
        $wsdl = $this->soapMockService->loadWsdl();

        return $this->createResponse($wsdl);
    }

    public function handleRequest(Request $request): Response
    {
        $this->auditService->setEventExpected(false);
        $xml = $this->soapMockService->handleRequest($request);

        return $this->createResponse($xml);
    }

    private function createResponse(string $content): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/xml');
        $response->setContent($content);

        return $response;
    }
}
