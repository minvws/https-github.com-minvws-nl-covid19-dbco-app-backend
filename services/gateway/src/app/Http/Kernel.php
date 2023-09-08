<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\PrometheusRouteMiddleware;
use App\Http\Middleware\ValidateJwtMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Routing\Middleware\SubstituteBindings;
use MinVWS\Audit\Middleware\Audit;
use MinVWS\Audit\Middleware\AuditRequests;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        PreventRequestsDuringMaintenance::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'api' => [
            SubstituteBindings::class,
            AuditRequests::class,
        ],
        'web' => [
            AuditRequests::class,
        ],
    ];

    protected $middlewareAliases = [
        'audit' => Audit::class,
        'audit.requests' => AuditRequests::class,
        'jwt' => ValidateJwtMiddleware::class,
        'prometheus' => PrometheusRouteMiddleware::class,
    ];

    protected $middlewarePriority = [
        PrometheusRouteMiddleware::class,
        ValidateJwtMiddleware::class,
        Audit::class,
        AuditRequests::class,
    ];
}
