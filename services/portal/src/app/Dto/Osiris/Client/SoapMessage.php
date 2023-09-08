<?php

declare(strict_types=1);

namespace App\Dto\Osiris\Client;

use App\Exceptions\Osiris\CouldNotConvertSoapMessageBody;
use SimpleXMLElement;

final class SoapMessage
{
    private const PROTOCOL_XMLV2 = 'xmlv2';

    public function __construct(
        private readonly Credentials $credentials,
        private readonly SimpleXMLElement $body,
        private readonly string $communicationId,
    ) {
    }

    public function getBody(): SimpleXMLElement
    {
        return clone $this->body;
    }

    public function getBodyAsXml(): string
    {
        $xml = $this->body->asXML();

        if ($xml === false) {
            throw new CouldNotConvertSoapMessageBody();
        }

        return $xml;
    }

    public function toArray(): array
    {
        return [
            'SysLogin' => $this->credentials->sysLogin,
            'SysPassword' => $this->credentials->sysPassword,
            'OsirisGebruikerLogin' => $this->credentials->userLogin,
            'CommunicatieId' => $this->communicationId,
            'Protocol' => self::PROTOCOL_XMLV2,
            'Message' => $this->getBodyAsXml(),
        ];
    }
}
