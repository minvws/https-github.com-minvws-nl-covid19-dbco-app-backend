<?php

declare(strict_types=1);

namespace App\Exceptions\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class UnauthorizedException extends RuntimeException implements Responsable
{
    public function toResponse($request): JsonResponse
    {
        $data = ['error' => $this->getMessage()];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
