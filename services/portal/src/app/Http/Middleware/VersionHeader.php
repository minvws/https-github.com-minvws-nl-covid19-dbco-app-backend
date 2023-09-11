<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\Config;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class VersionHeader
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-GGD-Contact-Version', Config::string('app.env_version'));

        return $response;
    }
}
