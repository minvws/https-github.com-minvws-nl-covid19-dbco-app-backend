<?php

namespace MinVWS\Audit\Middleware;

use Exception;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MinVWS\Audit\AuditService;
use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Http\Request as LaravelRequest;
use Psr\Http\Message\ServerRequestInterface as PSR7Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Makes sure HTTP requests are audited and audit events are finalized.
 *
 * @package MinVWS\Audit\Middleware
 */
class AuditRequests
{
    /**
     * @var AuditService
     */
    private AuditService $auditService;

    /**
     * Constructor.
     *
     * @param AuditService $auditService
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Check if Action has been audited. Middleware is used by the Laravel
     * portal as the Slim apis.
     *
     * @param  PSR7Request|LaravelRequest  $request Either a PSR-7 request or Laravel Request
     * @param  RequestHandler|Closure $handler Request handler
     *
     * @return Response
     */
    public function __invoke($request, $handler)
    {
        if ($handler instanceof RequestHandler) {
            $response = $handler->handle($request);
        } elseif ($handler instanceof Closure) {
            $response = $handler($request);
        }

        if ($request instanceof PSR7Request) {
            $requestUri = $request->getUri();
        } else {
            $requestUri = $request->getRequestUri();
        }

        // finalize current http event (if any)
        $this->auditService->finalizeHttpEvent($response);

        $statusCode = null;
        if ($response instanceof Response || method_exists($response, 'getStatusCode')) {
            $statusCode = $response->getStatusCode();
        } elseif (method_exists($response, 'status')) {
            $statusCode = $response->status();
        }

        if (
            $this->auditService->isEventExpected() &&
            !$this->auditService->isEventRegistered() &&
            ($statusCode >= 200 && $statusCode < 400) &&
            !Str::startsWith($requestUri, ['/clockwork', '/__clockwork/']) &&
            !is_null($statusCode)
        ) {
            throw new Exception("Audit event expected, but no audit event registered!");
        }
        return $response;
    }
}
