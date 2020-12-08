<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

/**
 * Security cache proxy that stores the values of the given security cache in memory.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class ProxySecurityCache implements SecurityCache
{
    /**
     * @var SecurityCache
     */
    private SecurityCache $cache;

    /**
     * @var array
     */
    private array $entries = [];

    /**
     * Constructor.
     *
     * @param SecurityCache $cache
     */
    public function __construct(SecurityCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function hasSecretKey(string $identifier): bool
    {
        return $this->getSecretKey($identifier) !== null;
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
        if (!array_key_exists($identifier, $this->entries)) {
            $this->entries[$identifier] = $this->cache->getSecretKey($identifier);
        }

        return $this->entries[$identifier];
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
        $this->cache->setSecretKey($identifier, $secretKey);
        $this->entries[$identifier] = $secretKey;
    }

    /**
     * Delete secret key with the given identifier.
     *
     * @param string $identifier
     */
    public function deleteSecretKey(string $identifier): void
    {
        $this->cache->deleteSecretKey($identifier);
        unset($this->entries[$identifier]);
    }

    /**
     * Reset
     */
    public function reset(): void
    {
        $this->entries = [];
    }
}
