<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Exceptions\Http\UnauthorizedException;
use App\Http\Middleware\ValidateJwtMiddleware;
use App\Services\JwtTokenService;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class ValidateJwtMiddlewareTest extends TestCase
{
    public function testUnauthorizedExceptionThrownForMissingAuthorizationHeader(): void
    {
        $jwtTokenService = new JwtTokenService('jwtSecret');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $middleware = new ValidateJwtMiddleware($jwtTokenService, $logger);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Authorization token not found');
        $middleware->handle(new Request(), function (Request $request) {
        });
    }

    public function testUnauthorizedExceptionThrownForInvalidAuthorizationHeader(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', $this->faker->unique()->password);

        $jwtTokenService = new JwtTokenService($this->faker->unique()->password);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $middleware = new ValidateJwtMiddleware($jwtTokenService, $logger);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid authorization token');
        $middleware->handle($request, function (Request $request) {
        });
    }
}
