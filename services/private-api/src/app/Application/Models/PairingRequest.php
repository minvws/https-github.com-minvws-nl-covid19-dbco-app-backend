<?php
namespace DBCO\PrivateAPI\Application\Models;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use RuntimeException;

/**
 * Pairing request model.
 */
class PairingRequest
{
    /**
     * Case UUID.
     *
     * @var string
     */
    public string $caseUuid;

    /**
     * Random generated pairing code that can be communicated out-of-band
     * to the patient.
     *
     * @var string
     */
    public string $code;

    /**
     * Expiration date/time for the pairing code. After this time the code will be threated as invalid and
     * it is not possible anymore to link the device to the case.
     *
     * @var DateTimeInterface
     */
    public DateTimeInterface $codeExpiresAt;

    /**
     * After the code is not valid anymore a warning will be shown when the user
     * tries to pair with a code that has expired until the given date/time.
     *
     * @var DateTimeInterface
     */
    public DateTimeInterface $codeExpiredWarningUntil;

    /**
     * Code is not available for pairing requests until this time.
     *
     * @var DateTimeInterface
     */
    public DateTimeInterface $codeBlockedUntil;

    /**
     * Pairing request constructor.
     *
     * @param string            $caseUuid
     * @param string            $code
     * @param DateTimeInterface $codeExpiresAt
     * @param DateTimeInterface $codeExpiredWarningUntil
     * @param DateTimeInterface $codeBlockedUntil
     */
    public function __construct(
        string $caseUuid,
        string $code,
        DateTimeInterface $codeExpiresAt,
        DateTimeInterface $codeExpiredWarningUntil,
        DateTimeInterface $codeBlockedUntil
    )
    {
        try {
            $this->caseUuid = $caseUuid;
            $this->code = $code;
            $tz = new DateTimeZone('UTC');
            $this->codeExpiresAt = new DateTimeImmutable('@' . $codeExpiresAt->getTimestamp(), $tz);
            $this->codeExpiredWarningUntil = new DateTimeImmutable('@' . $codeExpiredWarningUntil->getTimestamp(), $tz);
            $this->codeBlockedUntil = new DateTimeImmutable('@' . $codeBlockedUntil->getTimestamp(), $tz);
        } catch (Exception $e) {
            // should not be possible
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
