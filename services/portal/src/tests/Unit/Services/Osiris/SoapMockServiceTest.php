<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris;

use App\Http\Server\Soap\SoapServer;
use App\Services\Osiris\SoapMockService;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

final class SoapMockServiceTest extends TestCase
{
    public function testExceptionThrownWhenWsdlFileDoesNotExist(): void
    {
        $wsdlPath = $this->faker->filePath();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File does not exist: "' . $wsdlPath . '"');

        File::expects('isFile')->with($wsdlPath)->andReturn(false);

        $soapServer = $this->createMock(SoapServer::class);

        $soapMockService = new SoapMockService($soapServer, $wsdlPath);
        $soapMockService->loadWsdl();
    }

    public function testWsdlFileIsLoadedAndContentIsReturned(): void
    {
        $wsdlPath = $this->faker->filePath();
        $expected = $this->faker->word();

        File::expects('isFile')->with($wsdlPath)->andReturn(true);
        File::expects('get')->with($wsdlPath)->andReturn($expected);

        $soapServer = $this->createMock(SoapServer::class);

        $soapMockService = new SoapMockService($soapServer, $wsdlPath);
        $actual = $soapMockService->loadWsdl();

        $this->assertEquals($expected, $actual);
    }
}
