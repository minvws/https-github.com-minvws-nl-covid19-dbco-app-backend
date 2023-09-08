<?php

declare(strict_types=1);

namespace App\Services\Assignment\Exception\Http;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AssignmentInvalidTokenHttpException extends HttpResponseException implements AssignmentHttpException
{
    public function __construct()
    {
        parent::__construct($this->buildJsonResponse());
    }

    private function buildJsonResponse(): Response
    {
        $json = [
            'error' => [
                'message' => 'Invalid token provided!',
            ],
        ];

        return new JsonResponse($json, status: 400);
    }
}
