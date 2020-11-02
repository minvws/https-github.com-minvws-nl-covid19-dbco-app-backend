<?php
namespace DBCO\PrivateAPI\Application\Responses;

use DBCO\PrivateAPI\Application\Models\PairingRequest;
use DBCO\Shared\Application\Responses\Response;
use DateTimeZone;
use JsonSerializable;

/**
 * Response for the register case action.
 */
class CaseRegisterResponse extends Response implements JsonSerializable
{
    /**
     * @var PairingRequest
     */
    private PairingRequest $pairingRequest;

    /**
     * Constructor.
     *
     * @param PairingRequest $pairingRequest Pairing request.
     */
    public function __construct(PairingRequest $pairingRequest)
    {
        $this->pairingRequest = $pairingRequest;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'pairingCode' =>
                $this->pairingRequest->code,
            'pairingCodeExpiresAt' =>
                $this->pairingRequest->codeExpiresAt
                    ->setTimezone(new DateTimeZone('UTC'))
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
