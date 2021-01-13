<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Security cache.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
interface SecurityCache
{
    public const SK_KEY_EXCHANGE = 'key_exchange';

    public const SK_STORE_LEGACY = 'store';
    public const SK_STORE_TEMPLATE = 'store:%s';

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
}
