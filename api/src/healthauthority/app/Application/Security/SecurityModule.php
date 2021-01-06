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
    public const SK_KEY_EXCHANGE   = 'key_exchange';

    public const SK_STORE_LEGACY   = 'store';
    public const SK_STORE_TEMPLATE = 'store:%s';

    /**
     * Generate / store secret key.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function generateSecretKey(string $identifier): string;

    /**
     * Checks if the secret key exists.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasSecretKey(string $identifier): bool;

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
     * Lists all secret keys.
     *
     * @return string[] List of key identifiers.
     */
    public function listSecretKeys(): array;

    /**
     * Get random bytes.
     *
     * @param int $length
     *
     * @return string
     */
    public function randomBytes(int $length): string;

    /**
     * Generate nonce.
     *
     * @param int $length
     *
     * @return string
     */
    public function nonce(int $length): string;
}
