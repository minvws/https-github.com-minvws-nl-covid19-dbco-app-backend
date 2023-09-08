<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Events\FailedX509Authentication;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use MinVWS\Audit\Services\AuditService;

use function event;
use function route;

class Authenticate extends Middleware
{
    private AuditService $auditService;

    public function __construct(Auth $auth, AuditService $auditService)
    {
        parent::__construct($auth);

        $this->auditService = $auditService;
    }

    protected function redirectTo(Request $request): ?string
    {
        $this->auditService->setEventExpected(false);

        return $request->expectsJson() ? null : route('login');
    }

    /**
     * @inheritdoc
     */
    protected function unauthenticated($request, array $guards): void
    {
        if ($guards === ['export']) {
            event(new FailedX509Authentication());
        }

        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectTo($request),
        );
    }
}
