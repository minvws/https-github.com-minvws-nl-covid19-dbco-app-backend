<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Policy\PolicyVersion;
use App\Services\PolicyVersionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class PolicyVersionStatusCheck
{
    public function __construct(private readonly PolicyVersionService $policyVersionService)
    {
    }

    /**
     * @param Closure(Request):(Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $route = $request->route();

        Assert::isInstanceOf($route, Route::class);

        $policyVersion = $this->getPolicyVersion($route);
        if ($policyVersion === false) {
            return $next($request);
        }

        $this->policyVersionService->allowsMutations($policyVersion);

        return $next($request);
    }

    private function getPolicyVersion(Route $route): false|PolicyVersion
    {
        if (!$route->hasParameter('policy_version')) {
            return false;
        }

        $policyVersion = $route->parameter('policy_version');
        if (!$policyVersion instanceof PolicyVersion) {
            return false;
        }

        return $policyVersion;
    }
}
