<?php
namespace DBCO\Application\Models;

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
    public ?string $id;

    /**
     * Case model.
     *
     * @var DbcoCase
     */
    public DbcoCase $case;

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
     * Signing key.
     *
     * @var string|null
     */
    public ?string $signingKey;

    /**
     * Pairing constructor.
     *
     * @param string                 $id
     * @param DbcoCase|null          $case
     * @param string|null            $code
     * @param DateTimeInterface|null $codeExpiresAt
     * @param bool                   $isPaired
     * @param string|null            $signingKey
     */
    public function __construct(?string $id, ?DbcoCase $case, ?string $code, ?DateTimeInterface $codeExpiresAt, bool $isPaired, ?string $signingKey)
    {
        try {
            $this->id = $id;
            $this->case = $case;
            $this->code = $code;
            $this->codeExpiresAt = $codeExpiresAt != null ? new DateTimeImmutable('@' . $codeExpiresAt->getTimestamp()) : null;
            $this->isPaired = $isPaired;
            $this->signingKey = $signingKey;
        } catch (Exception $e) {
            // should not be possible
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
