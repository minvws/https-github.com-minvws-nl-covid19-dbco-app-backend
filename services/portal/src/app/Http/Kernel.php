<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckForRoles;
use App\Http\Middleware\DisableRouteCache;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\ExtendInactivityTimerCookie;
use App\Http\Middleware\FeatureFlag;
use App\Http\Middleware\Prometheus;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RemoveInactivityTimerCookie;
use App\Http\Middleware\RequireConsent;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustHosts;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateOpenAPISpec;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\VersionHeader;
use App\Services\Assignment\Middleware\AssignmentTokenMiddleware;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MinVWS\Audit\Middleware\Audit;
use MinVWS\Audit\Middleware\AuditObject;
use MinVWS\Audit\Middleware\AuditRequests;
use Spatie\Csp\AddCspHeaders;

class Kernel extends HttpKernel
{
    protected $middleware = [
        Prometheus::class,
        VersionHeader::class,
        TrustHosts::class,
        TrustProxies::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        SecurityHeaders::class,
        AddCspHeaders::class,
        ValidateOpenAPISpec::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            CheckForRoles::class,
            AuditRequests::class,
        ],

        // Frontend api, so we share the same middleware as web
        'api' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            AssignmentTokenMiddleware::class,
            SubstituteBindings::class,
            DisableRouteCache::class,
            AuditRequests::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'cache.headers' => SetCacheHeaders::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'consent' => RequireConsent::class,
        'audit' => Audit::class,
        'audit.object' => AuditObject::class,
        'audit.requests' => AuditRequests::class,
        'can' => Authorize::class,
        'featureflag' => FeatureFlag::class,
        'extend-inactivity-timer' => ExtendInactivityTimerCookie::class,
        'remove-inactivity-timer' => RemoveInactivityTimerCookie::class,
    ];

    protected $middlewarePriority = [
        Authenticate::class,
        AuthenticateWithBasicAuth::class,
        RedirectIfAuthenticated::class,
        SetCacheHeaders::class,
        RequirePassword::class,
        ValidateSignature::class,
        ThrottleRequests::class,
        EnsureEmailIsVerified::class,
        RequireConsent::class,
        Audit::class,
        AuditObject::class,
        AuditRequests::class,
        SubstituteBindings::class,
        Authorize::class,
    ];
}
