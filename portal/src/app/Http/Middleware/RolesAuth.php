<?php

namespace App\Http\Middleware;

use App\Services\AuthenticationService;
use Closure;
use Illuminate\Http\Request;

class RolesAuth
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if ($this->authService->hasRole($role)) {
            return $next($request);
        }
        $role = config('authorization.roles.'.$role);
        abort(403, "Voor deze pagina heb je minimaal de rol '".$role."' nodig.");
        return response('Unauthorized Action', 403);
    }
}
