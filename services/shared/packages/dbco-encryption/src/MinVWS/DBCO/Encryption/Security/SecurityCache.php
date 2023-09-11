<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Encryption\Security;

interface SecurityCache
{
    public const SK_STORE_CURRENT_IDENTIFIER = 'storeSecretKeyIdentifier';

    /**
     * Cache contains a value for the given key?
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasValue(string $key): bool;

    /**
     * Get value for the given key.
     *
     * @param string $key Key.
     *
     * @return string
     *
     * @throws CacheEntryNotFoundException
     */
    public function getValue(string $key): string;

    /**
     * Set value for the given key.
     *
     * @param string $key   Key.
     * @param string $value Value.
     */
    public function setValue(string $key, string $value): void;

    /**
     * Delete value with the given key.
     *
     * @param string $key Key.
     *
     * @return bool True if value was deleted, false if it didn't exist.
     */
    public function deleteValue(string $key): bool;

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
     * @return string
     *
     * @throws CacheEntryNotFoundException
     */
    public function getSecretKey(string $identifier): string;

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
     *
     * @return bool
     */
    public function deleteSecretKey(string $identifier): bool;
}
