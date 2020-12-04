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
    public function handle(Request $request, Closure $next)
    {
        if ($this->authService->hasUserRole()) {
            return $next($request);
        }
        $role = config('authorization.roles.user');
        abort(403, "Voor deze pagina heb je minimaal de rol '".$role."' nodig.");
        return response('Unauthorized Action', 403);
    }
}
