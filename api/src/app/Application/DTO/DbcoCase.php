<?php
namespace App\Application\DTO;

use App\Application\Models\DbcoCase as DbcoCaseModel;
use DateTimeInterface;

/**
 * DbcoCase DTO.
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
     * @var string
     */
    public string $pairingCodeExpiresAt;

    /**
     * DbcoCase constructor.
     *
     * @param string $id
     * @param string $pairingCode
     * @param string $pairingCodeExpiresAt
     */
    public function __construct(DbcoCaseModel $caseModel)
    {
        $this->caseId = $caseModel->caseId;
        $this->pairingCode = $caseModel->pairingCode;
        $this->pairingCodeExpiresAt = $caseModel->pairingCodeExpiresAt
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');
    }
}
