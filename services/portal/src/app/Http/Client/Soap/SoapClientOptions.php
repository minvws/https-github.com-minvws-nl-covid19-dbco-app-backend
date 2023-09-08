<?php

declare(strict_types=1);

namespace App\Http\Client\Soap;

use function array_filter;
use function stream_context_create;

use const SOAP_1_2;

final class SoapClientOptions
{
    public function __construct(
        private readonly string $serviceName,
        private readonly ?string $uri = null,
        private readonly ?int $connectionTimeout = null,
        private readonly ?int $timeout = null,
        private readonly ?int $cacheWsdl = null,
        private readonly string $encoding = 'UTF-8',
        private readonly int $soapVersion = SOAP_1_2,
    ) {
    }

    public function toArray(): array
    {
        $options = [
            'uri' => $this->uri,
            'connection_timeout' => $this->connectionTimeout,
            'cache_wsdl' => $this->cacheWsdl,
            'encoding' => $this->encoding,
            'soap_version' => $this->soapVersion,
            'trace' => true,
        ];

        if ($this->timeout) {
            $options['stream_context'] = stream_context_create([
                'http' => [
                    'timeout' => $this->timeout,
                ],
            ]);
        }

        return array_filter(
            $options,
            static function (mixed $value): bool {
                return $value !== null;
            },
        );
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }
}
