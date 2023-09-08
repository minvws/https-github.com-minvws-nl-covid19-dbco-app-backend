<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris;

use App\Dto\Osiris\Client\Credentials;
use App\Dto\Osiris\Client\PutMessageResult;
use App\Dto\Osiris\Client\SoapMessage;
use App\Exceptions\Osiris\Client\ClientException;
use App\Exceptions\Osiris\Client\ErrorResponseException;
use App\Http\Client\Soap\Exceptions\NotAvailableException;
use App\Http\Client\Soap\SoapClient;
use App\Services\Osiris\OsirisSoapClient;
use App\ValueObjects\OsirisNumber;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use SoapFault;
use stdClass;
use Tests\Unit\UnitTestCase;

#[Group('osiris')]
#[Group('osiris-case-export')]
final class OsirisSoapClientTest extends UnitTestCase
{
    private SoapClient $soapClient;
    private LoggerInterface $logger;
    private OsirisSoapClient $osirisClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->soapClient = Mockery::mock(SoapClient::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->osirisClient = new OsirisSoapClient($this->soapClient, $this->logger);

        $this->logger->expects('debug');
    }

    /**
     * @throws ClientException
     * @throws ErrorResponseException
     */
    public function testHandleSuccessResponse(): void
    {
        $soapMessage = $this->mockSoapMessage();
        $osirisNumber = $this->faker->randomNumber(6);

        $this->soapClient->expects('call')
            ->with('PutMessage', [$soapMessage->toArray()])
            ->andReturn($this->putMessageSuccessResponse($osirisNumber));

        $this->assertEquals(
            new PutMessageResult(new OsirisNumber($osirisNumber), []),
            $this->osirisClient->putMessage($soapMessage),
        );
    }

    /**
     * @throws ClientException
     * @throws ErrorResponseException
     */
    public function testHandleSuccessWithWarningsResponse(): void
    {
        $soapMessage = $this->mockSoapMessage();
        $osirisNumber = $this->faker->randomNumber(6);

        $this->soapClient->expects('call')
            ->with('PutMessage', [$soapMessage->toArray()])
            ->andReturn($this->putMessageSuccessWithWarningsResponse($osirisNumber));

        $this->assertEquals(
            new PutMessageResult(new OsirisNumber($osirisNumber), ['Warning 1', 'Warning 2', 'Warning 3']),
            $this->osirisClient->putMessage($soapMessage),
        );
    }

    /**
     * @throws ClientException
     * @throws ErrorResponseException
     */
    public function testHandleSoapFaultResponse(): void
    {
        $soapMessage = $this->mockSoapMessage();
        $soapFault = new SoapFault($this->faker->numerify(), $message = $this->faker->sentence());

        $this->soapClient->expects('call')
            ->with('PutMessage', [$soapMessage->toArray()])
            ->andThrows($soapFault);
        $this->soapClient->expects('getLastResponse')
            ->andReturn($message);

        $this->expectExceptionObject(ClientException::fromThrowable($soapFault));

        $this->logger->expects('error');

        $this->osirisClient->putMessage($soapMessage);
    }

    /**
     * @throws ClientException
     * @throws ErrorResponseException
     */
    public function testHandleErrorResponse(): void
    {
        $soapMessage = $this->mockSoapMessage();

        $this->soapClient->expects('call')
            ->with('PutMessage', [$soapMessage->toArray()])
            ->andReturn($this->mockErrorResponse(
                $reason = $this->faker->sentence(),
                '/.br error 1/.br error 2/.br error 3',
            ));

        $this->expectExceptionObject(
            new ErrorResponseException(
                $reason,
                ['Error 1', 'Error 2', 'Error 3'],
            ),
        );

        $this->osirisClient->putMessage($soapMessage);
    }

    /**
     * @throws ClientException
     * @throws ErrorResponseException
     */
    public function testHandleInvalidErrorResponse(): void
    {
        $soapMessage = $this->mockSoapMessage();

        $this->soapClient->expects('call')
            ->with('PutMessage', [$soapMessage->toArray()])
            ->andReturn($this->mockInvalidErrorResponse());

        $this->expectExceptionObject(
            new ErrorResponseException('Unknown', []),
        );

        $this->osirisClient->putMessage($soapMessage);
    }

    /**
     * @throws ClientException
     * @throws ErrorResponseException
     */
    public function testHandleResponseWithUnexpectedRoot(): void
    {
        $soapMessage = $this->mockSoapMessage();

        $this->soapClient->expects('call')
            ->with('PutMessage', [$soapMessage->toArray()])
            ->andReturn((object) ['unexpectedRoot' => 'unexpectedValue']);

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Missing PutMessageResult element');

        $this->osirisClient->putMessage($soapMessage);
    }

    /**
     * @throws ErrorResponseException
     */
    public function testHandleServiceNotAvailableSinceCircuitBreakerIsOpen(): void
    {
        $soapMessage = $this->mockSoapMessage();

        $this->soapClient->expects('call')
            ->with('PutMessage', [$soapMessage->toArray()])
            ->andThrows(NotAvailableException::circuitBreakerOpen());

        $this->logger->expects('error');

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('circuit breaker open');

        $this->osirisClient->putMessage($soapMessage);
    }

    private function putMessageSuccessResponse(int $osirisNumber): stdClass
    {
        $putMessageResult = <<<XML
<PutResultaat xmlns="http://tempuri.org/PutResultaat.xsd">
  <resultaat>
    <osiris_nummer>$osirisNumber</osiris_nummer>
    <bericht_ok>true</bericht_ok>
    <melding_ok>true</melding_ok>
  </resultaat>
</PutResultaat>
XML;

        $response = new stdClass();
        $response->PutMessageResult = $putMessageResult;

        return $response;
    }

    private function putMessageSuccessWithWarningsResponse(int $osirisNumber): stdClass
    {
        $putMessageResult = <<<XML
<PutResultaat xmlns="http://tempuri.org/PutResultaat.xsd">
  <resultaat>
    <osiris_nummer>$osirisNumber</osiris_nummer>
    <bericht_ok>true</bericht_ok>
    <melding_ok>true</melding_ok>
    <fout_toelichting>/.br--> warning 1/.br-->warning 2/.br--> warning 3</fout_toelichting>
  </resultaat>
</PutResultaat>
XML;

        $response = new stdClass();
        $response->PutMessageResult = $putMessageResult;

        return $response;
    }

    private function mockErrorResponse(string $errorReason, string $errorExplanation): stdClass
    {
        $xml = new SimpleXMLElement('<root/>');
        $result = $xml->addChild('resultaat');
        $result->addChild('bericht_ok', 'false');
        $result->addChild('melding_ok', 'false');
        $result->addChild('fout_reden', $errorReason);
        $result->addChild('fout_toelichting', $errorExplanation);

        $response = new stdClass();
        $response->PutMessageResult = $xml->asXML();

        return $response;
    }

    private function mockInvalidErrorResponse(): stdClass
    {
        $response = new stdClass();
        $response->PutMessageResult = '<root/>';

        return $response;
    }

    private function mockSoapMessage(): SoapMessage
    {
        return new SoapMessage(
            new Credentials('sysLogin', 'sysPassword', 'userLogin'),
            new SimpleXMLElement('<foobar/>'),
            $this->faker->bothify('?#?#?#?#?#?#'),
        );
    }
}
