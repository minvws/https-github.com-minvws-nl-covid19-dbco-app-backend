<?php

declare(strict_types=1);

namespace App\Encryption\Security;

use App\Helpers\Environment;
use MinVWS\DBCO\Encryption\Security\SecurityCache;
use RuntimeException;

class SecurityCacheFake implements SecurityCache
{
    public function __construct(
        private readonly string $key,
    ) {
        if (Environment::isProduction()) {
            throw new RuntimeException('Running fake security module in production environment...');
        }
    }

    public function hasValue(string $key): bool
    {
        return true;
    }

    public function getValue(string $key): string
    {
        return $this->key;
    }

    public function setValue(string $key, string $value): void
    {
    }

    public function deleteValue(string $key): bool
    {
        return true;
    }

    public function hasSecretKey(string $identifier): bool
    {
        return true;
    }

    public function getSecretKey(string $identifier): string
    {
        return $this->key;
    }

    public function setSecretKey(string $identifier, string $secretKey): void
    {
    }

    public function deleteSecretKey(string $identifier): bool
    {
        return true;
    }
}
