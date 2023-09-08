<?php

namespace MinVWS\DBCO\PairingRequest\Models;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * Pairing request instantiated by the health authority.
 */
abstract class PairingRequest
{
    /**
     * Random generated pairing code that can be communicated out-of-band.
     *
     * @var string|null
     */
    public ?string $code;

    /**
     * Expiration date/time for the pairing request. After this time the code will be treated as invalid and
     * it is not possible anymore to link the device to the case.
     *
     * @var DateTimeInterface
     */
    public DateTimeInterface $expiresAt;

    /**
     * After the request is not valid anymore a warning will be shown when the user
     * tries to pair with a code/token that has expired until the given date/time.
     *
     * @var DateTimeInterface
     */
    public DateTimeInterface $expiredWarningUntil;

    /**
     * Constructor.
     *
     * @param string|null       $code
     * @param DateTimeInterface $expiresAt
     * @param DateTimeInterface $expiredWarningUntil
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function __construct(
        ?string $code,
        DateTimeInterface $expiresAt,
        DateTimeInterface $expiredWarningUntil
    ) {
        $this->code = $code;
        $tz = new DateTimeZone('UTC');
        $this->expiresAt = new DateTimeImmutable('@' . $expiresAt->getTimestamp(), $tz);
        $this->expiredWarningUntil = new DateTimeImmutable('@' . $expiredWarningUntil->getTimestamp(), $tz);
    }
}
