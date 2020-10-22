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
     * Signing key.
     *
     * @var string|null
     */
    public string $signingKey;

    /**
     * Pairing constructor.
     *
     * @param PairingCase $case
     * @param string      $signingKey
     */
    public function __construct(PairingCase $case, string $signingKey)
    {
        $this->case = $case;
        $this->signingKey = $signingKey;
    }
}
