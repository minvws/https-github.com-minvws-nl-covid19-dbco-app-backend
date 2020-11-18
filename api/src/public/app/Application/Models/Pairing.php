<?php
namespace DBCO\PublicAPI\Application\Models;

/**
 * Pairing model.
 */
class Pairing
{
    /**
     * Case.
     *
     * @var PairingCase
     */
    public PairingCase $case;

    /**
     * Sealed client public key.
     *
     * @var string|null
     */
    public ?string $sealedClientPublicKey;


    /**
     * Sealed health authority public key.
     *
     * @var string|null
     */
    public ?string $sealedHealthAuthorityPublicKey;

    /**
     * Pairing constructor.
     *
     * @param PairingCase $case
     * @param string|null $sealedClientPublicKey
     * @param string|null $sealedHealthAuthorityPublicKey
     */
    public function __construct(PairingCase $case, ?string $sealedClientPublicKey, ?string $sealedHealthAuthorityPublicKey = null)
    {
        $this->case = $case;
        $this->sealedClientPublicKey = $sealedClientPublicKey;
        $this->sealedHealthAuthorityPublicKey = $sealedHealthAuthorityPublicKey;
    }
}
