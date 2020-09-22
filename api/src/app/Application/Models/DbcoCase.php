<?php
namespace App\Application\Models;

/**
 * Case model.
 */
class DbcoCase
{
    /**
     * Primary key
     *
     * @var int
     */
    public int $id;

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
     * @var \DateTimeImmutable
     */
    public \DateTimeImmutable $pairingCodeExpiresAt;

    /**
     * DbcoCase constructor.
     *
     * @param string $id
     * @param string $pairingCode
     * @param string $pairingCodeExpiresAt
     */
    public function __construct(int $id, string $caseId, string $pairingCode, string $pairingCodeExpiresAt)
    {
        $this->id = $id;
        $this->caseId = $caseId;
        $this->pairingCode = $pairingCode;
        $this->pairingCodeExpiresAt = new \DateTimeImmutable($pairingCodeExpiresAt);
    }
}
