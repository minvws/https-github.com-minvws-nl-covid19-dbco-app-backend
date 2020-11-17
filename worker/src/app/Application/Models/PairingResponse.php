<?php
namespace DBCO\Worker\Application\Models;

/**
 * Represents a pairing response from the health authority.
 */
class PairingResponse
{
    /**
     * @var PairingRequest
     */
    public PairingRequest $request;

    /**
     * @var string
     */
    public string $sealedHealthAuthorityPublicKey;

    /**
     * Constructor.
     *
     * @param PairingRequest $request
     * @param string         $sealedHealthAuthorityPublicKey
     */
    public function __construct(PairingRequest $request, string $sealedHealthAuthorityPublicKey)
    {
        $this->request = $request;
        $this->sealedHealthAuthorityPublicKey = $sealedHealthAuthorityPublicKey;
    }
}
