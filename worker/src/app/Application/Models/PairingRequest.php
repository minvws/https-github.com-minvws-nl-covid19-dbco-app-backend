<?php
namespace DBCO\Worker\Application\Models;

/**
 * Represents a pairing request from the client.
 */
class PairingRequest
{
    /**
     * @var PairingRequestCase
     */
    public PairingRequestCase $case;

    /**
     * @var string
     */
    public string $sealedClientPublicKey;

    /**
     * Constructor.
     *
     * @param PairingRequestCase $case
     * @param string             $sealedClientPublicKey
     */
    public function __construct(PairingRequestCase $case, string $sealedClientPublicKey)
    {
        $this->case = $case;
        $this->sealedClientPublicKey = $sealedClientPublicKey;
    }
}
