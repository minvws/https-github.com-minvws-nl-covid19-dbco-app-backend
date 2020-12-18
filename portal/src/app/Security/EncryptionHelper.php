<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Utility methods for encryption.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Helpers
 */
class EncryptionHelper
{
    /**
     * Constructor.
     *
     * @param SecurityCache $securityCache
     */
    public function __construct(SecurityCache $securityCache)
    {
        $this->securityCache = $securityCache;
    }

    /**
     * Unseal value from the store using the general store encryption key.
     *
     * @param string $sealedValue Sealed value.
     *
     * @return string Unsealed value.
     */
    public function unsealStoreValue(string $sealedValue): string
    {
        $data = json_decode($sealedValue);
        $ciphertext = base64_decode($data->ciphertext);
        $nonce = base64_decode($data->nonce);
        $key = $this->securityCache->getSecretKey(SecurityCache::SK_STORE);
        return sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
    }

    /**
     * Unseal value from the store using the general store encryption key.
     *
     * @param string|null $sealedValue Sealed value.
     *
     * @return string|null Unsealed value.
     */
    public function unsealOptionalStoreValue(?string $sealedValue): ?string
    {
        if ($sealedValue === null) {
            return null;
        } else {
            return $this->unsealStoreValue($sealedValue);
        }
    }
}
