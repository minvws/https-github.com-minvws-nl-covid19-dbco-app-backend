<?php

declare(strict_types=1);

namespace App\Http\Client\Soap;

use SoapClient;
use SoapFault;

class NativeSoapClientFactory
{
    /**
     * @throws SoapFault
     */
    public function create(?string $wsdl, array $options): SoapClient
    {
        return new SoapClient($wsdl, $options);
    }
}
