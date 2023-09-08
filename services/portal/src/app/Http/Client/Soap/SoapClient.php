<?php

declare(strict_types=1);

namespace App\Http\Client\Soap;

use App\Http\Client\Soap\Exceptions\SoapClientException;
use SoapFault;

interface SoapClient
{
    /**
     * @throws SoapClientException
     * @throws SoapFault
     */
    public function call(string $method, array $arguments): object;

    public function getLastResponse(): ?string;

    public function getServiceName(): string;
}
