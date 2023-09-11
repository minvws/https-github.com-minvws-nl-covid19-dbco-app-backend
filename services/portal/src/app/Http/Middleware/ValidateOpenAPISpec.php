<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Events\OpenAPI\OpenAPIValidationFailedEvent;
use App\Helpers\Environment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

use function strtolower;
use function throw_if;

class ValidateOpenAPISpec extends Middleware
{
    public function __construct(
        private readonly ValidatorBuilder $builder,
        private readonly PsrHttpFactory $psrHttpFactory,
        #[Config('openapi.specification')]
        private readonly string $pathToSpecification,
        #[Config('openapi.throw_exceptions')]
        private readonly bool $throwsExceptions,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (Environment::isProduction()) {
            return $response;
        }

        if ($response->getStatusCode() === 404) {
            return $response;
        }

        $operation = new OperationAddress('/' . $request->path(), strtolower($request->method()));
        $validator = $this->builder->fromYamlFile($this->pathToSpecification)->getResponseValidator();
        $psrResponse = $this->psrHttpFactory->createResponse($response);

        try {
            $validator->validate($operation, $psrResponse);
        } catch (NoPath) {
            return $response;
        } catch (ValidationFailed $e) {
            OpenAPIValidationFailedEvent::dispatch($e, $operation);
            throw_if($this->throwsExceptions, $e);
        }

        return $response;
    }
}
