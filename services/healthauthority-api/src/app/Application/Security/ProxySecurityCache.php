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
     * @inheritDoc
     */
    public function setValue(string $key, string $value): void
    {
        $this->cache->setValue($key, $value);
        $this->values[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function deleteValue(string $key): bool
    {
        $result = $this->cache->deleteValue($key);
        unset($this->values[$key]);
        return $result;
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
     * @inheritdoc
     */
    public function setSecretKey(string $identifier, string $secretKey): void
    {
        $this->cache->setSecretKey($identifier, $secretKey);
        $this->secretKeys[$identifier] = $secretKey;
    }

    /**
     * @inheritdoc
     */
    public function deleteSecretKey(string $identifier): bool
    {
        $result = $this->cache->deleteSecretKey($identifier);
        unset($this->secretKeys[$identifier]);
        return $result;
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
