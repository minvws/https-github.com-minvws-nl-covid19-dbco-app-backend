<?php
namespace DBCO\PublicAPI\Application\Responses;

use DBCO\PublicAPI\Application\Models\Pairing;
use DBCO\Shared\Application\Responses\Response;
use JsonSerializable;

/**
 * Response for pairing completion.
 */
class PairingResponse extends Response implements JsonSerializable
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
            'sealedHealthAuthorityPublicKey' => base64_encode($this->pairing->sealedHealthAuthorityPublicKey)
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
