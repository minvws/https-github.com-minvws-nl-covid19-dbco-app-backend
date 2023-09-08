<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Encryption\Security;

class FakeSecurityCache implements SecurityCache
{
    private $cache = [];

    private $secretKeys = [];

    public function hasValue(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    public function getValue(string $key): string
    {
        if (!$this->hasValue($key)) {
            throw new CacheEntryNotFoundException("Cache entry not found for key: $key");
        }

        return $this->cache[$key];
    }

    public function setValue(string $key, string $value): void
    {
        $this->cache[$key] = $value;
    }

    public function deleteValue(string $key): bool
    {
        if (!$this->hasValue($key)) {
            return false;
        }

        unset($this->cache[$key]);

        return true;
    }

    public function hasSecretKey(string $identifier): bool
    {
        return isset($this->secretKeys[$identifier]);
    }

    public function getSecretKey(string $identifier): string
    {
        if (!$this->hasSecretKey($identifier)) {
            throw new CacheEntryNotFoundException("Secret key not found for identifier: $identifier");
        }

        return $this->secretKeys[$identifier];
    }

    public function setSecretKey(string $identifier, string $secretKey): void
    {
        $this->secretKeys[$identifier] = $secretKey;
    }

    public function deleteSecretKey(string $identifier): bool
    {
        if (!$this->hasSecretKey($identifier)) {
            return false;
        }

        unset($this->secretKeys[$identifier]);

        return true;
    }
}
