<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

/**
 * Security module.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
interface SecurityModule
{
    public const SK_KEY_EXCHANGE = 'key_exchange';

    public const SK_STORE        = 'store';
    public const SK_STORE_NEW    = 'store_new';

    /**
     * Generate / store secret key.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function generateSecretKey(string $identifier): string;

    /**
     * Get secret key for the given identifier.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function getSecretKey(string $identifier): string;

    /**
     * Delete secret key.
     *
     * @param string $identifier
     */
    public function deleteSecretKey(string $identifier): void;

    /**
     * Rename key.
     *
     * @param string $oldIdentifier
     * @param string $newIdentifier
     *
     * @return mixed
     */
    public function renameSecretKey(string $oldIdentifier, string $newIdentifier);

    /**
     * Get random bytes.
     *
     * @param int $length
     *
     * @return string
     */
    public function randomBytes(int $length): string;
}
