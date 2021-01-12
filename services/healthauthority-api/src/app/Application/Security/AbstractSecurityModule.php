<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

/**
 * Security module.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
abstract class AbstractSecurityModule implements SecurityModule
{
    /**
     * @var bool
     */
    private bool $usePhpRandomBytesForNonce;

    /**
     * Constructor.
     *
     * @param bool $usePhpRandomBytesForNonce
     */
    public function __construct(bool $usePhpRandomBytesForNonce)
    {
        $this->usePhpRandomBytesForNonce = $usePhpRandomBytesForNonce;
    }

    /**
     * Generate nonce.
     *
     * @param int $length
     *
     * @return string
     */
    public function nonce(int $length): string
    {
        if ($this->usePhpRandomBytesForNonce) {
            return random_bytes($length);
        } else {
            return $this->randomBytes($length);
        }
    }
}
