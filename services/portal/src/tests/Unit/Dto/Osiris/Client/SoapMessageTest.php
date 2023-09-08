<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Osiris\Client;

use App\Dto\Osiris\Client\Credentials;
use App\Dto\Osiris\Client\SoapMessage;
use App\Exceptions\Osiris\CouldNotConvertSoapMessageBody;
use SimpleXMLElement;
use Tests\Mocks\InvalidSimpleXmlElement;
use Tests\Unit\UnitTestCase;

use const PHP_EOL;

class SoapMessageTest extends UnitTestCase
{
    public function testToArrayMapsDtoToSoapMessageAttributes(): void
    {
        $credentials = new Credentials(
            $sysLogin = $this->faker->word(),
            $sysPassword = $this->faker->word(),
            $userLogin = $this->faker->word(),
        );
        $body = new SimpleXMLElement('<foobar/>');
        $communicationId = $this->faker->bothify('?#?#?#?#?#?#');
        $soapMessage = new SoapMessage($credentials, $body, $communicationId);

        $this->assertEquals(
            [
                'SysLogin' => $sysLogin,
                'SysPassword' => $sysPassword,
                'OsirisGebruikerLogin' => $userLogin,
                'CommunicatieId' => $communicationId,
                'Protocol' => 'xmlv2',
                'Message' => $body->asXML(),
            ],
            $soapMessage->toArray(),
        );
    }

    public function testGetBodyAsXmlThrowsExceptionIfXmlInvalid(): void
    {
        $soapMessage = new SoapMessage(
            new Credentials($this->faker->word(), $this->faker->word(), $this->faker->word()),
            new InvalidSimpleXmlElement('<invalid/>'),
            $this->faker->bothify('?#?#?#?#?#?#'),
        );

        $this->expectException(CouldNotConvertSoapMessageBody::class);

        $soapMessage->getBodyAsXml();
    }

    public function testGetBodyAsXmlReturnsStringBody(): void
    {
        $soapMessage = new SoapMessage(
            new Credentials($this->faker->word(), $this->faker->word(), $this->faker->word()),
            new SimpleXMLElement('<foobar></foobar>'),
            $this->faker->bothify('?#?#?#?#?#?#'),
        );

        $this->assertStringContainsString('<?xml version="1.0"?>' . PHP_EOL . '<foobar/>' . PHP_EOL, $soapMessage->getBodyAsXml());
    }
}
