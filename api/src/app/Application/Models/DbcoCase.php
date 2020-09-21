<?php
namespace App\Application\Models;

/**
 * Case model.
 */
class DbcoCase
{
    /**
     * Random generated case identifier.
     *
     * @var string
     */
    public string $id;

    /**
     * Random generated link code that can be communicated out-of-band
     * to the patient.
     *
     * @var string
     */
    public string $linkCode;

    /**
     * Expiration date/time for the link code. After this time it is not
     * possible anymore to link the device to the case. Datetime is stored
     * in UTC.
     *
     * @var \DateTime
     */
    public \DateTimeImmutable $linkCodeExpiresAt;

    /**
     * Constructor.
     *
     * @param string $id
     * @param string $status
     */
    public function __construct(string $id, string $linkCode, string $linkCodeExpiresAt)
    {
        $this->id = $id;
        $this->status = $linkCode;
        $this->linkCodeExpiresAt = new \DateTimeImmutable($linkCodeExpiresAt);
    }
}
