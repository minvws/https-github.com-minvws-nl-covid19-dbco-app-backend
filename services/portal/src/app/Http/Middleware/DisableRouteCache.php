<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableRouteCache
{
    public function handle(Request $request, Closure $next): mixed
    {
        return $next($request)->withHeaders([
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache, must-revalidate, no-store, max-age=0, private',
        ]);
    }
}
