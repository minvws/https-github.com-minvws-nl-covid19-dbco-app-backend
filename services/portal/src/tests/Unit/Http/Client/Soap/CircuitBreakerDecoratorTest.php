<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Client\Soap;

use App\Http\CircuitBreaker\CircuitBreaker;
use App\Http\Client\Soap\CircuitBreakerDecorator;
use App\Http\Client\Soap\Exceptions\SoapClientException;
use App\Http\Client\Soap\SoapClient;
use App\Models\Metric\CircuitBreaker\Availability;
use App\Repositories\Metric\MetricRepository;
use App\Services\CircuitBreakerService;
use App\Services\MetricService;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use SoapFault;
use stdClass;
use Tests\Unit\UnitTestCase;

final class CircuitBreakerDecoratorTest extends UnitTestCase
{
    public function testCallWhenCircuitBreakerAvailable(): void
    {
        $service = $this->faker->word();
        $method = $this->faker->word();
        $arguments = [$this->faker->optional()->word(), $this->faker->boolean];
        $response = new stdClass();

        $circuitBreaker = $this->createMock(CircuitBreaker::class);
        $circuitBreaker->expects($this->once())
            ->method('isAvailable')
            ->with($service)
            ->willReturn(true);
        $circuitBreaker->expects($this->once())
            ->method('registerSuccess')
            ->with($service);

        $soapClient = $this->createMock(SoapClient::class);
        $soapClient->expects($this->once())
            ->method('getServiceName')
            ->willReturn($service);
        $soapClient->expects($this->once())
            ->method('call')
            ->with($method, $arguments)
            ->willReturn($response);

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureGauge')
            ->with(Availability::available($service));

        $circuitBreakerDecorator = $this->getCircuitBreakerDecorator($soapClient, $circuitBreaker, $metricRepository);

        $this->assertSame($response, $circuitBreakerDecorator->call($method, $arguments));
    }

    public function testCallWhenCircuitBreakerNotAvailable(): void
    {
        $service = $this->faker->word();
        $method = $this->faker->word();

        $circuitBreaker = $this->createMock(CircuitBreaker::class);
        $circuitBreaker->expects($this->once())
            ->method('isAvailable')
            ->with($service)
            ->willReturn(false);

        $soapClient = $this->createMock(SoapClient::class);
        $soapClient->expects($this->once())
            ->method('getServiceName')
            ->willReturn($service);
        $soapClient->expects($this->never())
            ->method('call');

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureGauge')
            ->with(Availability::notAvailable($service));

        $circuitBreakerDecorator = $this->getCircuitBreakerDecorator($soapClient, $circuitBreaker, $metricRepository);

        $this->expectException(SoapClientException::class);
        $this->expectExceptionMessage('circuit breaker open');

        $circuitBreakerDecorator->call($method, []);
    }

    public function testRegisterFailureWhenCallFailsWithSoapFault(): void
    {
        $service = $this->faker->word();
        $method = $this->faker->word();
        $soapFault = new SoapFault($this->faker->numerify(), $this->faker->word());
        $lastResponse = $soapFault->getMessage();

        $circuitBreaker = $this->createMock(CircuitBreaker::class);
        $circuitBreaker->expects($this->once())
            ->method('isAvailable')
            ->with($service)
            ->willReturn(true);
        $circuitBreaker->expects($this->once())
            ->method('registerFailure')
            ->with($service);

        $soapClient = $this->createMock(SoapClient::class);
        $soapClient->expects($this->once())
            ->method('getServiceName')
            ->willReturn($service);
        $soapClient->expects($this->once())
            ->method('call')
            ->with($method)
            ->willThrowException($soapFault);

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureGauge')
            ->with(Availability::available($service));

        $circuitBreakerDecorator = $this->getCircuitBreakerDecorator($soapClient, $circuitBreaker, $metricRepository);

        $this->expectException(SoapFault::class);
        $this->expectExceptionMessage($lastResponse);

        $circuitBreakerDecorator->call($method, []);
    }

    public function testDoNotRegisterAnythingWhenCallFailsWithExceptionAndNoRequestIsMade(): void
    {
        $service = $this->faker->word();
        $method = $this->faker->word();
        $exception = new Exception($this->faker->sentence);

        /** @var CircuitBreaker|MockObject $circuitBreaker */
        $circuitBreaker = $this->createMock(CircuitBreaker::class);
        $circuitBreaker->expects($this->once())
            ->method('isAvailable')
            ->with($service)
            ->willReturn(true);
        $circuitBreaker->expects($this->never())
            ->method('registerSuccess');
        $circuitBreaker->expects($this->never())
            ->method('registerFailure');

        $soapClient = $this->createMock(SoapClient::class);
        $soapClient->expects($this->once())
            ->method('getServiceName')
            ->willReturn($service);
        $soapClient->expects($this->once())->method('call')
            ->with($method)
            ->willThrowException($exception);

        $circuitBreakerDecorator = $this->getCircuitBreakerDecorator(
            $soapClient,
            $circuitBreaker,
            $this->createMock(MetricRepository::class),
        );

        $this->expectExceptionObject($exception);
        $circuitBreakerDecorator->call($method, []);
    }

    public function testGetLastResponse(): void
    {
        $service = $this->faker->word();
        $method = $this->faker->word();
        $arguments = [$this->faker->optional()->word(), $this->faker->boolean];
        $lastResponse = '<?xml/>';

        $circuitBreaker = $this->createMock(CircuitBreaker::class);
        $circuitBreaker->expects($this->once())
            ->method('isAvailable')
            ->with($service)
            ->willReturn(true);
        $circuitBreaker->expects($this->once())
            ->method('registerSuccess')
            ->with($service);

        $soapClient = $this->createMock(SoapClient::class);
        $soapClient->expects($this->once())
            ->method('getServiceName')
            ->willReturn($service);
        $soapClient->expects($this->once())
            ->method('call')
            ->with($method, $arguments)
            ->willReturn(new stdClass());
        $soapClient->expects($this->once())
            ->method('getLastResponse')
            ->willReturn($lastResponse);

        $circuitBreakerDecorator = $this->getCircuitBreakerDecorator(
            $soapClient,
            $circuitBreaker,
            $this->createMock(MetricRepository::class),
        );

        $circuitBreakerDecorator->call($method, $arguments);
        $this->assertEquals($lastResponse, $circuitBreakerDecorator->getLastResponse());
    }

    public function getCircuitBreakerDecorator(
        SoapClient $soapClient,
        CircuitBreaker $circuitBreaker,
        MetricRepository $metricRepository,
    ): CircuitBreakerDecorator {
        return new CircuitBreakerDecorator(
            $soapClient,
            new CircuitBreakerService(
                $circuitBreaker,
                new MetricService(new NullLogger(), $metricRepository),
            ),
        );
    }
}
