<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\Http\UnauthorizedException;
use App\Services\JwtTokenService;
use Closure;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ValidateJwtMiddleware
{
    private JwtTokenService $jwtTokenService;
    private LoggerInterface $logger;

    public function __construct(JwtTokenService $jwtTokenService, LoggerInterface $logger)
    {
        $this->jwtTokenService = $jwtTokenService;
        $this->logger = $logger;
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->jwtTokenService->getJwtPayloadFromRequest($request);
        } catch (Throwable $throwable) {
            $this->logger->warning('Failed to validate JWT', ['trace' => $throwable]);
            throw new UnauthorizedException($throwable->getMessage());
        }

        return $next($request);
    }
}
