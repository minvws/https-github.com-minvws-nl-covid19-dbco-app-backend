<?php

namespace MinVWS\DBCO\PairingRequest\Models;

use DateTime;
use DateTimeInterface;
use JsonSerializable;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodingContainer;

/**
 * Index pairing request.
 */
class IndexPairingRequest extends PairingRequest implements JsonSerializable, Decodable
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Token that can be used to check the pairing status.
     *
     * @var string
     */
    public string $token;

    /**
     * @var string
     */
    public string $status;

    /**
     * @var HealthAuthorityPairingRequest|null
     */
    public ?HealthAuthorityPairingRequest $healthAuthorityPairingRequest;

    /**
     * Constructor.
     *
     * @param string                             $token
     * @param string                             $status
     * @param string|null                        $code
     * @param DateTimeInterface|null             $expiresAt
     * @param DateTimeInterface|null             $expiredWarningUntil
     * @param HealthAuthorityPairingRequest|null $healthAuthorityPairingRequest
     */
    public function __construct(
        string $token,
        string $status,
        ?string $code,
        DateTimeInterface $expiresAt,
        DateTimeInterface $expiredWarningUntil,
        ?HealthAuthorityPairingRequest $healthAuthorityPairingRequest = null
    ) {
        parent::__construct($code, $expiresAt, $expiredWarningUntil);
        $this->token = $token;
        $this->status = $status;
        $this->healthAuthorityPairingRequest = $healthAuthorityPairingRequest;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'token' => $this->token,
            'status' => $this->status,
            'code' => $this->code,
            'expiresAt' => $this->expiresAt->format(DateTime::ATOM),
            'expiredWarningUntil' => $this->expiredWarningUntil->format(DateTime::ATOM),
            'healthAuthorityPairingRequest' => $this->healthAuthorityPairingRequest
        ];
    }

    /**
     * @inheritDoc
     */
    public static function decode(DecodingContainer $container, ?object $object = null): Decodable
    {
        $token = $container->token->decodeString();
        $status = $container->status->decodeString();
        $code = $container->code->decodeStringIfPresent();
        $expiresAt = $container->expiresAt->decodeDateTime(DateTime::ATOM);
        $expiredWarningUntil = $container->expiredWarningUntil->decodeDateTime(DateTime::ATOM);
        $healthAuthorityPairingRequest = $container->healthAuthorityPairingRequest->decodeObjectIfPresent(HealthAuthorityPairingRequest::class);
        return new static($token, $status, $code, $expiresAt, $expiredWarningUntil, $healthAuthorityPairingRequest);
    }
}
