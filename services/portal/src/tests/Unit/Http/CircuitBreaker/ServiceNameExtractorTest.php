<?php

declare(strict_types=1);

namespace Tests\Unit\Http\CircuitBreaker;

use App\Http\CircuitBreaker\Exceptions\ServiceNameNotConfiguredException;
use App\Http\CircuitBreaker\ServiceNameExtractor;
use Tests\Unit\UnitTestCase;

final class ServiceNameExtractorTest extends UnitTestCase
{
    public function testExtractServiceNameFromOption(): void
    {
        $serviceName = $this->faker->word();
        $options = ['service_name' => $serviceName];

        $serviceNameExtractor = new ServiceNameExtractor();
        $actual = $serviceNameExtractor->extract($options);

        $this->assertEquals($serviceName, $actual);
    }

    public function testExceptionThrownWhenNoServiceNameOptionIsConfigured(): void
    {
        $options = [];
        $serviceNameExtractor = new ServiceNameExtractor();

        $this->expectException(ServiceNameNotConfiguredException::class);
        $this->expectExceptionMessage('No "service_name" option configured for client');

        $serviceNameExtractor->extract($options);
    }
}
