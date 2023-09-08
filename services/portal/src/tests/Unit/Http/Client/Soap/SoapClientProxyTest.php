<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Client\Soap;

use App\Http\Client\Soap\NativeSoapClientFactory;
use App\Http\Client\Soap\SoapClientOptions;
use App\Http\Client\Soap\SoapClientProxy;
use SoapClient;
use stdClass;
use Tests\Unit\UnitTestCase;

final class SoapClientProxyTest extends UnitTestCase
{
    public function testCallSingleSoapCallOnNativeClient(): void
    {
        $wsdl = $this->faker->url();
        $service = $this->faker->word();

        $response = new stdClass();
        $response->PutMessageResult = '</resultaat>';

        $arguments = ['foo' => 'x', 'bar' => 'y'];

        $soapClientOptions = new SoapClientOptions($service);
        $nativeSoapClient = $this->createMock(SoapClient::class);
        $nativeSoapClient->expects($this->once())->method('__call')
            ->with('PutMessage', $arguments)
            ->willReturn($response);

        $nativeSoapClientFactory = $this->createMock(NativeSoapClientFactory::class);
        $nativeSoapClientFactory->expects($this->once())->method('create')
            ->with($wsdl, $soapClientOptions->toArray())
            ->willReturn($nativeSoapClient);

        $soapClientProxy = new SoapClientProxy($nativeSoapClientFactory, $wsdl, $soapClientOptions);
        $actual = $soapClientProxy->call('PutMessage', $arguments);
        $this->assertSame($response, $actual);
    }

    public function testCallMultipleSoapCallsOnNativeClientWhichIsInstantiatedOnce(): void
    {
        $wsdl = $this->faker->url();
        $fooArguments = ['foo'];
        $barArguments = ['bar'];

        $fooResponse = new stdClass();
        $fooResponse->foo = 'foo';

        $barResponse = new stdClass();
        $barResponse->bar = 'bar';

        $service = $this->faker->word();

        $soapClientOptions = new SoapClientOptions($service);

        $nativeSoapClient = $this->createMock(SoapClient::class);
        $nativeSoapClient->expects($this->exactly(2))->method('__call')
            ->willReturnMap(
                [
                    ['foo', $fooArguments, $fooResponse],
                    ['bar', $barArguments, $barResponse],
                ],
            );

        $nativeSoapClientFactory = $this->createMock(NativeSoapClientFactory::class);
        $nativeSoapClientFactory->expects($this->once())->method('create')
            ->with($wsdl, $soapClientOptions->toArray())
            ->willReturn($nativeSoapClient);

        $soapClientProxy = new SoapClientProxy($nativeSoapClientFactory, $wsdl, $soapClientOptions);
        $actual = $soapClientProxy->call('foo', $fooArguments);
        $this->assertSame($fooResponse, $actual);

        $actual = $soapClientProxy->call('bar', $barArguments);
        $this->assertSame($barResponse, $actual);
    }

    public function testGetLastResponseFromNativeClientAfterSoapCall(): void
    {
        $wsdl = $this->faker->url();
        $arguments = ['foo' => 'x', 'bar' => 'y'];
        $response = new stdClass();
        $service = $this->faker->word();
        $lastResponse = $this->faker->word();

        $soapClientOptions = new SoapClientOptions($service);

        $nativeSoapClient = $this->createMock(SoapClient::class);
        $nativeSoapClient->expects($this->once())->method('__call')
            ->with('putMessage', $arguments)
            ->willReturn($response);
        $nativeSoapClient->expects($this->once())->method('__getLastResponse')
            ->willReturn($lastResponse);

        $nativeSoapClientFactory = $this->createMock(NativeSoapClientFactory::class);
        $nativeSoapClientFactory->expects($this->once())->method('create')
            ->with($wsdl, $soapClientOptions->toArray())
            ->willReturn($nativeSoapClient);

        $soapClientProxy = new SoapClientProxy($nativeSoapClientFactory, $wsdl, $soapClientOptions);
        $soapClientProxy->call('putMessage', $arguments);
        $this->assertSame($lastResponse, $soapClientProxy->getLastResponse());
    }

    public function testGetLastResponseNotCalledWhenNoSoapCallIsMade(): void
    {
        $wsdl = $this->faker->url();
        $service = $this->faker->word();

        $soapClientOptions = new SoapClientOptions($service);

        $nativeSoapClientFactory = $this->createMock(NativeSoapClientFactory::class);

        $soapClientProxy = new SoapClientProxy($nativeSoapClientFactory, $wsdl, $soapClientOptions);
        $this->assertNull($soapClientProxy->getLastResponse());
    }

    public function testRetrieveServiceNameFromSoapClientOptions(): void
    {
        $wsdl = $this->faker->url();
        $service = $this->faker->word();

        $soapClientOptions = new SoapClientOptions($service);
        $nativeSoapClientFactory = $this->createMock(NativeSoapClientFactory::class);

        $soapClientProxy = new SoapClientProxy($nativeSoapClientFactory, $wsdl, $soapClientOptions);
        $this->assertSame($service, $soapClientProxy->getServiceName());
    }
}
