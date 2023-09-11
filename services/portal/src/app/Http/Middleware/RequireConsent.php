<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Eloquent\EloquentUser;
use Closure;
use Illuminate\Http\Request;
use MinVWS\Audit\Services\AuditService;

use function redirect;

readonly class RequireConsent
{
    public function __construct(
        private AuditService $auditService,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        /** @var ?EloquentUser $user */
        $user = $request->user();

        if (
            $request->method() === Request::METHOD_GET
            && $user !== null
            && $user->consented_at === null
            && !$request->routeIs('consent-show')
        ) {
            $this->auditService->setEventExpected(false);

            return redirect()->route('consent-show');
        }

        return $next($request);
    }
}
