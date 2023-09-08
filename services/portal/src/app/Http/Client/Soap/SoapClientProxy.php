<?php

declare(strict_types=1);

namespace App\Http\Client\Soap;

use SoapClient as NativeSoapClient;
use SoapFault;

final class SoapClientProxy implements SoapClient
{
    private ?NativeSoapClient $nativeSoapClient = null;

    public function __construct(
        private readonly NativeSoapClientFactory $nativeSoapClientFactory,
        private readonly string $wsdl,
        private readonly SoapClientOptions $soapClientOptions,
    ) {
    }

    /**
     * @throws SoapFault
     */
    public function call(string $method, array $arguments): object
    {
        $response = $this->getSoapClient()->$method(...$arguments);

        return (object) $response;
    }

    public function getLastResponse(): ?string
    {
        return $this->nativeSoapClient?->__getLastResponse();
    }

    public function getServiceName(): string
    {
        return $this->soapClientOptions->getServiceName();
    }

    /**
     * @throws SoapFault
     */
    private function getSoapClient(): NativeSoapClient
    {
        if ($this->nativeSoapClient === null) {
            $this->nativeSoapClient = $this->nativeSoapClientFactory->create(
                $this->wsdl,
                $this->soapClientOptions->toArray(),
            );
        }

        return $this->nativeSoapClient;
    }
}
