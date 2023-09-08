<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Eloquent\EloquentUser;
use Closure;
use Illuminate\Http\Request;

use function abort;

final class CheckForRoles
{
    private array $excludedRoutes = [
        'user-profile',
        'user-logout',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        /** @var ?EloquentUser $user */
        $user = $request->user();

        if ($user !== null && $user->roles === null && !$request->routeIs($this->excludedRoutes)) {
            abort(403);
        }

        return $next($request);
    }
}
