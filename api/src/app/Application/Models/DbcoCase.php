<?php
namespace App\Application\Models;

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
    public string $caseId;

    /**
     * Random generated link code that can be communicated out-of-band
     * to the patient.
     *
     * @var string
     */
    public string $pairingCode;

    /**
     * Expiration date/time for the link code. After this time it is not
     * possible anymore to link the device to the case. Datetime is stored
     * in UTC.
     *
     * @var \DateTime
     */
    public \DateTimeImmutable $pairingCodeExpiresAt;

    /**
     * Constructor.
     *
     * @param string $id
     * @param string $status
     */
    public function __construct(string $id, string $pairingCode, string $pairingCodeExpiresAt)
    {
        $this->caseId = $id;
        $this->pairingCode = $pairingCode;
        $this->pairingCodeExpiresAt = new \DateTimeImmutable($pairingCodeExpiresAt);
    }
}
