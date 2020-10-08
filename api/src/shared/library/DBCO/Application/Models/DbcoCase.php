<?php
namespace DBCO\Application\Models;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use RuntimeException;

/**
 * Case model.
 */
class DbcoCase
{
    /**
     * Case identifier from the GGD system
     *
     * @var string
     */
    public string $id;

    /**
     * Expiration date/time for the case. After this time it is not
     * possible anymore to submit data for this case.
     *
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $expiresAt;

    /**
     * Constructor.
     *
     * @param string            $id
     * @param DateTimeInterface $expiresAt
     */
    public function __construct(string $id, DateTimeInterface $expiresAt)
    {
        try {
            $this->id = $id;
            $this->expiresAt = new DateTimeImmutable('@' . $expiresAt->getTimestamp());
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
