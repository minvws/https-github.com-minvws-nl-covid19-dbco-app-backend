<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\FeatureFlagHelper;
use Closure;
use Illuminate\Http\Request;

use function abort_if;

/**
 * Aborts request if the given feature is disabled
 */
class FeatureFlag
{
    public function handle(Request $request, Closure $next, string $actionCode): mixed
    {
        abort_if(FeatureFlagHelper::isDisabled($actionCode), 403);

        return $next($request);
    }
}
