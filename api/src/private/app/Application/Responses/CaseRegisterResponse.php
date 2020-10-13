<?php
namespace App\Application\Responses;

use DBCO\Application\Models\Pairing;
use DBCO\Application\Responses\Response;
use JsonSerializable;

/**
 * Response for the register case action.
 */
class CaseRegisterResponse extends Response implements JsonSerializable
{
    /**
     * @var Pairing
     */
    private Pairing $pairing;

    /**
     * Constructor.
     *
     * @param Pairing $pairing Pairing.
     */
    public function __construct(Pairing $pairing)
    {
        $this->pairing = $pairing;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'pairingCode' =>
                $this->pairing->code,
            'pairingCodeExpiresAt' =>
                $this->pairing->codeExpiresAt
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->format('Y-m-d\TH:i:s\Z')
        ];
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return 201;
    }
}
