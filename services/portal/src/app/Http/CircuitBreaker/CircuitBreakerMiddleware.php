<?php

declare(strict_types=1);

namespace App\Http\CircuitBreaker;

use App\Http\CircuitBreaker\Exceptions\NotAvailableException;
use App\Services\CircuitBreakerService;
use GuzzleHttp\Promise\Create;
use Psr\Http\Message\RequestInterface;

final class CircuitBreakerMiddleware
{
    public function __construct(
        private readonly CircuitBreakerService $circuitBreakerService,
        private readonly ServiceNameExtractor $serviceNameExtractor,
    ) {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $service = $this->serviceNameExtractor->extract($options);

            $isAvailable = $this->circuitBreakerService->isAvailable($service);
            $this->circuitBreakerService->measureAvailability($service, $isAvailable);

            if (!$isAvailable) {
                return Create::rejectionFor(new NotAvailableException($service));
            }

            return $handler($request, $options)
                ->then(
                    function ($value) use ($service) {
                        $this->circuitBreakerService->registerSuccess($service);

                        return Create::promiseFor($value);
                    },
                    function ($reason) use ($service) {
                        $this->circuitBreakerService->registerFailure($service);

                        return Create::rejectionFor($reason);
                    },
                );
        };
    }
}
