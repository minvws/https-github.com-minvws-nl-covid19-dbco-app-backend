<?php
namespace DBCO\PublicAPI\Application\Models;

/**
 * Pairing model.
 */
class Pairing
{
    /**
     * Case UUID.
     *
     * @var string
     */
    public string $caseUuid;

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
     * @param string      $caseUuid
     * @param string|null $sealedClientPublicKey
     * @param string|null $sealedHealthAuthorityPublicKey
     */
    public function __construct(string $caseUuid, ?string $sealedClientPublicKey, ?string $sealedHealthAuthorityPublicKey = null)
    {
        $this->caseUuid = $caseUuid;
        $this->sealedClientPublicKey = $sealedClientPublicKey;
        $this->sealedHealthAuthorityPublicKey = $sealedHealthAuthorityPublicKey;
    }
}
