<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Client\Soap;

use App\Http\Client\Soap\SoapClientOptions;
use Tests\Unit\UnitTestCase;

use const SOAP_1_2;

final class SoapClientOptionsTest extends UnitTestCase
{
    public function testDefaultValues(): void
    {
        $defaultValues = [
            'encoding' => 'UTF-8',
            'soap_version' => SOAP_1_2,
            'trace' => true,
        ];

        $soapClientOptions = new SoapClientOptions(serviceName: $this->faker->word());
        $array = $soapClientOptions->toArray();

        $this->assertEquals($defaultValues, $array);
    }

    public function testToArrayFiltersNullValue(): void
    {
        $soapClientOptions = new SoapClientOptions(
            serviceName: $this->faker->word(),
            connectionTimeout: null,
            cacheWsdl: 0,
            encoding: 'UTF-8',
            soapVersion: SOAP_1_2,
        );

        $array = $soapClientOptions->toArray();

        $this->assertArrayNotHasKey('connection_timeout', $array);
    }

    public function testToArrayFiltersDoesNotFilter0Value(): void
    {
        $soapClientOptions = new SoapClientOptions(
            serviceName: $this->faker->word(),
            connectionTimeout: null,
            cacheWsdl: 0,
            encoding: 'UTF-8',
            soapVersion: SOAP_1_2,
        );

        $array = $soapClientOptions->toArray();

        $this->assertArrayHasKey('cache_wsdl', $array);
    }

    public function testTimeout(): void
    {
        $soapClientOptions = new SoapClientOptions(serviceName: $this->faker->word(), timeout: 5);

        $array = $soapClientOptions->toArray();
        $this->assertArrayHasKey('stream_context', $array);
    }
}
