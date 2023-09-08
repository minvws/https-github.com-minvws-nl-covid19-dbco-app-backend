<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

use function response;

abstract class ApiController extends Controller
{
    protected function createErrorResponseFromException(
        Throwable $exception,
        string $message,
        int $statusCode = Response::HTTP_BAD_REQUEST,
    ): JsonResponse {
        Log::error($message, ['exception' => $exception]);

        return response()
            ->json(['error' => $message])
            ->setStatusCode($statusCode);
    }
}
