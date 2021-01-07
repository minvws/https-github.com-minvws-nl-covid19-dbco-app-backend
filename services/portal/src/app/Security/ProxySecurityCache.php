<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Security cache proxy that caches data retrieved from another security cache in memory.
 *
 * @package App\Security
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
    private array $values = [];

    /**
     * @var array
     */
    private array $secretKeys = [];

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
     * @inheritDoc
     */
    public function hasValue(string $key): bool
    {
        try {
            $this->getvalue($key);
            return true;
        } catch (CacheEntryNotFoundException $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getValue(string $key): string
    {
        if (!array_key_exists($key, $this->values)) {
            $this->values[$key] = $this->cache->getValue($key);
        }

        return $this->values[$key];
    }

    /**
     * @inheritdoc
     */
    public function hasSecretKey(string $identifier): bool
    {
        try {
            $this->getSecretKey($identifier);
            return true;
        } catch (CacheEntryNotFoundException $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): string
    {
        if (!array_key_exists($identifier, $this->secretKeys)) {
            $this->secretKeys[$identifier] = $this->cache->getSecretKey($identifier);
        }

        return $this->secretKeys[$identifier];
    }

    /**
     * Reset
     */
    public function reset(): void
    {
        $this->values = [];
        $this->secretKeys = [];
    }
}
