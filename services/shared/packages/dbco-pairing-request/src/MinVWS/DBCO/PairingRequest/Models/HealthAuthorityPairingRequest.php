<?php

namespace MinVWS\DBCO\PairingRequest\Models;

use DateTime;
use DateTimeInterface;
use JsonSerializable;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

/**
 * Pairing request instantiated by the health authority.
 */
class HealthAuthorityPairingRequest extends PairingRequest implements JsonSerializable, Decodable
{
    /**
     * Case UUID.
     *
     * @var string
     */
    public string $caseUuid;

    /**
     * Pairing request constructor.
     *
     * @param string            $caseUuid
     * @param string            $code
     * @param DateTimeInterface $expiresAt
     * @param DateTimeInterface $expiredWarningUntil
     */
    public function __construct(
        string $caseUuid,
        string $code,
        DateTimeInterface $expiresAt,
        DateTimeInterface $expiredWarningUntil
    ) {
        parent::__construct($code, $expiresAt, $expiredWarningUntil);
        $this->caseUuid = $caseUuid;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'caseUuid' => $this->caseUuid,
            'code' => $this->code,
            'expiresAt' => $this->expiresAt->format(DateTime::ATOM),
            'expiredWarningUntil' => $this->expiredWarningUntil->format(DateTime::ATOM)
        ];
    }

    /**
     * @inheritDoc
     */
    public static function decode(DecodingContainer $container, ?object $object = null): Decodable
    {
        $caseUuid = $container->caseUuid->decodeString();
        $code = $container->code->decodeString();
        $expiresAt = $container->expiresAt->decodeDateTime(DateTime::ATOM);
        $expiredWarningUntil = $container->expiredWarningUntil->decodeDateTime(DateTime::ATOM);
        return new static($caseUuid, $code, $expiresAt, $expiredWarningUntil);
    }
}
