<?php

declare(strict_types=1);

namespace App\Http\Client\Soap;

use App\Http\Client\Soap\Exceptions\SoapClientException;
use SoapFault;

abstract class SoapClientDecorator implements SoapClient
{
    public function __construct(
        protected readonly SoapClient $soapClient,
    ) {
    }

    /**
     * @throws SoapClientException
     * @throws SoapFault
     */
    public function call(string $method, array $arguments): object
    {
        return $this->soapClient->call($method, $arguments);
    }

    final public function getLastResponse(): ?string
    {
        return $this->soapClient->getLastResponse();
    }

    final public function getServiceName(): string
    {
        return $this->soapClient->getServiceName();
    }
}
