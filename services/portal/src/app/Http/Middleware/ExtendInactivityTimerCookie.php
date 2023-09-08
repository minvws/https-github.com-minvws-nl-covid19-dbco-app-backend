<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\SessionManager;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use function cookie;

/**
 * This cookie is set to forcefully logout the use when there is no activity.
 * We need a cookie to synchronise the expiryDate between tabs.
 *
 * The cookies is set when a page is visited that needs a logged in user
 * and when the frontend registers activity from the user
 *
 * The cookie us unset when the user is logged out.
 *
 * Note: the inactivity-timer expiry date is not always equal to the sesion expiry date.
 * Calls to endpoint that do not use this middleware will create a new session, but not
 * a new activity-timer expiry date.
 *
 * It will start to deviate when the api is called (endpoint without this middleware) but
 * there is no user interaction. When there is user activity; the two expiry dates are synced again.
 *
 * The result of this slight deviation is only seen when:
 * 1. the user has no interaction with the app for a while (but polling is used on that page)
 * 2. closes the page without interaction
 * 3. the session will expire not 30 minutes after the last interaction, but 30 minutes after the
 * last api call.
 */
class ExtendInactivityTimerCookie
{
    private SessionManager $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        /** @var Response $response */
        $response = $next($request);

        $sessionConfig = $this->sessionManager->getSessionConfig();
        $sessionLifetime = $sessionConfig['lifetime'];
        $sessionExpiryDate = CarbonImmutable::now()->addMinutes($sessionLifetime);

        $response->withCookie(cookie('InactivityTimerExpiryDate', $sessionExpiryDate->toISOString(), 0, null, null, false, false, true));

        return $response;
    }
}
