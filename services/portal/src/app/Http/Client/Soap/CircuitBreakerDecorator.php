<?php

declare(strict_types=1);

namespace App\Http\Client\Soap;

use App\Http\Client\Soap\Exceptions\NotAvailableException;
use App\Http\Client\Soap\Exceptions\SoapClientException;
use App\Services\CircuitBreakerService;
use SoapFault;

final class CircuitBreakerDecorator extends SoapClientDecorator
{
    public function __construct(
        SoapClient $soapClient,
        private readonly CircuitBreakerService $circuitBreakerService,
    ) {
        parent::__construct($soapClient);
    }

    /**
     * @throws NotAvailableException
     * @throws SoapClientException
     * @throws SoapFault
     */
    public function call(string $method, array $arguments): object
    {
        $service = $this->getServiceName();

        $isAvailable = $this->circuitBreakerService->isAvailable($service);
        $this->circuitBreakerService->measureAvailability($service, $isAvailable);

        if (!$isAvailable) {
            throw NotAvailableException::circuitBreakerOpen();
        }

        try {
            $response = parent::call($method, $arguments);
        } catch (SoapFault $soapFault) {
            $this->circuitBreakerService->registerFailure($service);
            throw $soapFault;
        }

        $this->circuitBreakerService->registerSuccess($service);
        return $response;
    }
}
