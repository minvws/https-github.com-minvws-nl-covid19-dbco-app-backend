<?php
namespace App\Application\Models;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use RuntimeException;

/**
 * Pairing model.
 */
class Pairing
{
    /**
     * Identifier.
     *
     * @var string
     */
    public string $id;

    /**
     * Case identifier from the GGD system.
     *
     * @var string
     */
    public string $caseId;

    /**
     * Random generated link code that can be communicated out-of-band
     * to the patient.
     *
     * @var string|null
     */
    public ?string $code;

    /**
     * Expiration date/time for the link code. After this time it is not
     * possible anymore to link the device to the case. Datetime is stored
     * in UTC.
     *
     * @var DateTimeImmutable|null
     */
    public ?DateTimeImmutable $codeExpiresAt;

    /**
     * Is paired?
     *
     * @var bool
     */
    public bool $isPaired;

    /**
     * Pairing constructor.
     *
     * @param string            $id
     * @param string            $caseId
     * @param string            $code
     * @param DateTimeInterface $codeExpiresAt
     * @param bool              $isPaired
     */
    public function __construct(string $id, string $caseId, string $code, DateTimeInterface $codeExpiresAt, bool $isPaired)
    {
        try {
            $this->id = $id;
            $this->caseId = $caseId;
            $this->code = $code;
            $this->codeExpiresAt = new DateTimeImmutable('@' . $codeExpiresAt->getOffset());
            $this->isPaired = $isPaired;
        } catch (Exception $e) {
            // should not be possible
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
