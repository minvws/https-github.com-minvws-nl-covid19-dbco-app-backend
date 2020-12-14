<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Security cache proxy that caches data retrieved from another security cache in memory.
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
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): ?string
    {
        if (!array_key_exists($identifier, $this->entries)) {
            $this->entries[$identifier] = $this->cache->getSecretKey($identifier);
        }

        return $this->entries[$identifier];
    }

    /**
     * Reset the proxy.
     */
    public function reset()
    {
        $this->entries = [];
    }
}
