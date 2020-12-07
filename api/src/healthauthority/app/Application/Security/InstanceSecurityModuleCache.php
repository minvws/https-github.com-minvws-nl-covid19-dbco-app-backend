<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

/**
 * Security module cache that stores its entries in an instance variable.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class InstanceSecurityModuleCache implements SecurityModuleCache
{
    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @inheritdoc
     */
    public function hasSecretKey(string $identifier): bool
    {
        return array_key_exists($identifier, $this->cache);
    }

    /**
     * Get secret key for the given identifier.
     *
     * @param string $identifier
     *
     * @return string|null
     */
    public function getSecretKey(string $identifier): ?string
    {
        return $this->cache[$identifier] ?? null;
    }

    /**
     * Store secret key with the given identifier.
     *
     * @param string $identifier
     * @param string $secretKey
     *
     * @return void
     */
    public function setSecretKey(string $identifier, string $secretKey): void
    {
        $this->cache[$identifier] = $secretKey;
    }

    /**
     * Delete secret key with the given identifier.
     *
     * @param string $identifier
     */
    public function deleteSecretKey(string $identifier): void
    {
        unset($this->cache[$identifier]);
    }
}
