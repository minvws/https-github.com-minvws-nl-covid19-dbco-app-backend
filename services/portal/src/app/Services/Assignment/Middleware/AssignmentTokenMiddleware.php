<?php

declare(strict_types=1);

namespace App\Services\Assignment\Middleware;

use App\Services\Assignment\AssignmentTokenAuthService;
use App\Services\Assignment\HasAssignmentToken;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function is_null;

class AssignmentTokenMiddleware
{
    public function __construct(
        private AssignmentTokenAuthService $tokenAuth,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response|RedirectResponse) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!is_null($user) && $user instanceof HasAssignmentToken && $this->tokenAuth->hasToken()) {
            $token = $this->tokenAuth->getToken();

            if ($user->uuid === $token->sub) {
                $user->setToken($token);
            }
        }

        return $next($request);
    }
}
