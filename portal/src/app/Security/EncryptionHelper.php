<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Utility methods for encryption.
 *
 * @package App\Security\Helpers
 */
class EncryptionHelper
{
    /**
     * @var SecurityCache
     */
    private SecurityCache $securityCache;

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
     * Seal store value using the general store encryption key.
     *
     * @param string $value Value.
     *
     * @return string Sealed value.
     */
    public function sealStoreValue(string $value): string
    {
        $secretKeyIdentifier = $this->securityCache->getValue(SecurityCache::SK_STORE_CURRENT_IDENTIFIER);
        return $this->sealStoreValueWithKey($value, $secretKeyIdentifier);
    }

    /**
     * Seal store value using the requested key.
     *
     * @param string $value               Value.
     * @param string $secretKeyIdentifier Key identifier of the secret key to be used.
     *
     * @return string Sealed value.
     */
    private function sealStoreValueWithKey(string $value, string $secretKeyIdentifier): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key = $this->securityCache->getSecretKey($secretKeyIdentifier);
        $ciphertext = sodium_crypto_secretbox($value, $nonce, $key);
        return json_encode([
            'ciphertext' => base64_encode($ciphertext),
            'nonce' => base64_encode($nonce),
            'key' => $secretKeyIdentifier
        ]);
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
        $identifier = $data->key ?? SecurityCache::SK_STORE_LEGACY;
        $key = $this->securityCache->getSecretKey($identifier);
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
