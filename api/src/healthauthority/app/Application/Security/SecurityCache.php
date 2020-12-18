<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

/**
 * Security cache.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
interface SecurityCache
{
    /**
     * Cache contains the secret key with the given identifier?
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
     * @return string|null
     */
    public function getSecretKey(string $identifier): ?string;

    /**
     * Store secret key with the given identifier.
     *
     * @param string $identifier
     * @param string $secretKey
     *
     * @return void
     */
    public function setSecretKey(string $identifier, string $secretKey): void;

    /**
     * Delete secret key with the given identifier.
     *
     * @param string $identifier
     */
    public function deleteSecretKey(string $identifier): void;
}
