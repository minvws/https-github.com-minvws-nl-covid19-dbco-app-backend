<?php
namespace DBCO\PrivateAPI\Application\Models;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use RuntimeException;

/**
 * Pairing request model.
 */
class PairingRequest
{
    /**
     * Case.
     *
     * @var PairingCase
     */
    public PairingCase $case;

    /**
     * Random generated pairing code that can be communicated out-of-band
     * to the patient.
     *
     * @var string
     */
    public string $code;

    /**
     * Expiration date/time for the pairing code. After this time it is not
     * possible anymore to link the device to the case. Datetime is stored
     * in UTC.
     *
     * @var DateTimeImmutable|null
     */
    public ?DateTimeImmutable $codeExpiresAt;

    /**
     * Pairing request constructor.
     *
     * @param PairingCase       $case
     * @param string            $code
     * @param DateTimeInterface $codeExpiresAt
     */
    public function __construct(PairingCase $case, string $code, DateTimeInterface $codeExpiresAt)
    {
        try {
            $this->case = $case;
            $this->code = $code;
            $this->codeExpiresAt = new DateTimeImmutable('@' . $codeExpiresAt->getTimestamp());
        } catch (Exception $e) {
            // should not be possible
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
