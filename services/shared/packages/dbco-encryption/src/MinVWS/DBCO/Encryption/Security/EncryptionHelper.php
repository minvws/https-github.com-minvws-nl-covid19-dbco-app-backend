<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Encryption\Security;

use DateTimeInterface;
use DBCO\Shared\Application\Models\SealedData;
use SodiumException;

/**
 * Utility methods for encryption.
 */
class EncryptionHelper
{
    private SecurityModule $securityModule;
    private SecurityCache $securityCache;

    public function __construct(SecurityModule $securityModule, SecurityCache $securityCache)
    {
        $this->securityModule = $securityModule;
        $this->securityCache = $securityCache;
    }

    /**
     * Create a key pair.
     *
     * @throws SodiumException
     */
    public function createHealthAuthorityKeyPair(): string
    {
        $seed = $this->securityModule->randomBytes(SODIUM_CRYPTO_KX_SEEDBYTES);
        return sodium_crypto_kx_seed_keypair($seed);
    }

    /**
     * Returns the public key for the given key pair.
     *
     * @throws SodiumException
     */
    public function getHealthAuthorityPublicKey(string $keyPair): string
    {
        return sodium_crypto_kx_publickey($keyPair);
    }

    /**
     * Returns the secret key for the given key pair.
     *
     * @throws SodiumException
     */
    public function getHealthAuthoritySecretKey(string $keyPair): string
    {
        return sodium_crypto_kx_secretkey($keyPair);
    }

    /**
     * Unseal the given data using the key with the given identifier.
     *
     * @param string $data       Sealed data.
     * @param string $identifier Key identifier.
     *
     * @return string Unsealed data.
     *
     * @throws SodiumException
     */
    public function unsealDataWithKey(string $data, string $identifier): string
    {
        $secretKey = $this->securityCache->getSecretKey($identifier);
        $publicKey = sodium_crypto_box_publickey_from_secretkey($secretKey);
        $keyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey($secretKey, $publicKey);

        $result = sodium_crypto_box_seal_open($data, $keyPair);
        if ($result === false) {
            throw new SodiumException('Failed to unseal data');
        }

        return $result;
    }

    /**
     * Unseal the given data with a derived key based on the local secret key and a remote public key.
     *
     * @param string $data               Sealed data, prefixed with nonce.
     * @param string $localKeyIdentifier Key identifier for the local key.
     * @param string $externalPublicKey  External public key.
     *
     * @throws SodiumException
     */
    public function unsealDataWithDerivedKey(string $data, string $localKeyIdentifier, string $externalPublicKey): string
    {
        $nonce = substr($data, 0, SODIUM_CRYPTO_BOX_NONCEBYTES);
        $encryptedData = substr($data, SODIUM_CRYPTO_BOX_NONCEBYTES);
        $localSecretKey = $this->securityCache->getSecretKey($localKeyIdentifier);
        $decryptionKey = sodium_crypto_box_keypair_from_secretkey_and_publickey($localSecretKey, $externalPublicKey);

        $result = sodium_crypto_box_open($encryptedData, $nonce, $decryptionKey);
        if ($result === false) {
            throw new SodiumException('Failed to unseal data');
        }

        return $result;
    }

    /**
     * Unseal client public key using the general health authority key pair.
     *
     * @param string $sealedClientPublicKey
     *
     * @return string Unsealed client public key.
     *
     * @throws SodiumException
     */
    public function unsealClientPublicKey(string $sealedClientPublicKey): string
    {
        return $this->unsealDataWithKey($sealedClientPublicKey, SecurityModule::SK_KEY_EXCHANGE);
    }

    /**
     * Derive shared secret keys for the given case key pair and client public key.
     *
     * @return array Array containing rx and tx shared secret keys.
     *
     * @throws SodiumException
     */
    public function deriveSharedSecretKeys(string $caseKeyPair, string $clientPublicKey): array
    {
        // calculate shared secret keys, client can do the same using our public key using
        // the following code: sodium_crypto_kx_client_session_keys($clientKeyPair, $casePublicKey)
        return sodium_crypto_kx_server_session_keys($caseKeyPair, $clientPublicKey);
    }

    /**
     * Derive shared token.
     *
     * @throws SodiumException
     */
    public function deriveSharedToken(string $receiveKey, string $transmitKey): string
    {
        // client should do the opposite, e.g. using the calculated secret keys
        // the client should call sodium_crypto_generichash($receiveKey . $transmitKey)
        // and should end up with the same hash
        return bin2hex(sodium_crypto_generichash($transmitKey . $receiveKey));
    }

    /**
     * Seal health authority public key using the client public key.
     *
     * @return string Sealed public key.
     *
     * @throws SodiumException
     */
    public function sealHealthAuthorityPublicKeyForClient(string $healthAuthorityPublicKey, string $clientPublicKey): string
    {
        return sodium_crypto_box_seal($healthAuthorityPublicKey, $clientPublicKey);
    }

    /**
     * Seal message for client.
     *
     * @throws SodiumException
     */
    public function sealMessageForClient(string $message, string $transferKey): SealedData
    {
        $nonce = $this->securityModule->randomBytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($message, $nonce, $transferKey);
        return new SealedData($ciphertext, $nonce);
    }

    /**
     * Unseal message from client.
     *
     * @throws SodiumException
     */
    public function unsealMessageFromClient(SealedData $sealedMessage, string $receiveKey): string
    {
        return sodium_crypto_secretbox_open($sealedMessage->ciphertext, $sealedMessage->nonce, $receiveKey);
    }

    /**
     * Seal store value using the general store encryption key.
     *
     * NOTE:
     * We could support passing in a StorageTermUnit instead of a separate term and reference date/time,
     * but passing both makes it easier to see the exact storage terms used when searching for this method's usage.
     *
     * @param string $value Value.
     * @param StorageTerm $storageTerm Storage term (StorageTerm::short() or StorageTerm::long()).
     * @param DateTimeInterface $referenceDateTime Reference date/time for selecting the appropriate storage term key.
     *
     * @return string Sealed value.
     *
     * @throws EncryptionException
     */
    public function sealStoreValue(string $value, StorageTerm $storageTerm, DateTimeInterface $referenceDateTime): string
    {
        $storageTermUnit = $storageTerm->unitForDateTime($referenceDateTime);
        $secretKeyIdentifier = sprintf(SecurityModule::SK_STORE_TEMPLATE, (string)$storageTermUnit);
        return $this->sealStoreValueWithKey($value, $secretKeyIdentifier);
    }

    /**
     * Seal store value if the value is non-null.
     *
     * NOTE:
     * We could support passing in a StorageTermUnit instead of a separate term and reference date/time,
     * but passing both makes it easier to see the exact storage terms used when searching for this method's usage.
     *
     * @param string|null $value Value.
     * @param StorageTerm $storageTerm Storage term (StorageTerm::short() or StorageTerm::long()).
     * @param DateTimeInterface $referenceDateTime Reference date/time for selecting the appropriate storage term key.
     *
     * @return string Sealed value.
     *
     * @throws EncryptionException
     */
    public function sealOptionalStoreValue(?string $value, StorageTerm $storageTerm, DateTimeInterface $referenceDateTime): ?string
    {
        if ($value === null) {
            return null;
        } else {
            return $this->sealStoreValue($value, $storageTerm, $referenceDateTime);
        }
    }

    public function hasStoreKey(StorageTerm $storageTerm, DateTimeInterface $referenceDateTime): bool
    {
        $storageTermUnit = $storageTerm->unitForDateTime($referenceDateTime);
        $secretKeyIdentifier = sprintf(SecurityModule::SK_STORE_TEMPLATE, (string)$storageTermUnit);
        return $this->securityCache->hasSecretKey($secretKeyIdentifier);
    }

    /**
     * Seal store value using the requested key.
     *
     * @param string $value Value.
     * @param string $secretKeyIdentifier Key identifier of the secret key to be used.
     *
     * @return string Sealed value.
     *
     * @throws EncryptionException
     */
    public function sealStoreValueWithKey(string $value, string $secretKeyIdentifier): string
    {
        $nonce = $this->securityModule->randomBytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        try {
            $key = $this->securityCache->getSecretKey($secretKeyIdentifier);
            $ciphertext = sodium_crypto_secretbox($value, $nonce, $key);
        } catch (SodiumException $exception) {
            throw new EncryptionException($exception->getMessage(), $exception->getCode(), $exception);
        }

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
     *
     * @throws CacheEntryNotFoundException
     * @throws SodiumException
     */
    public function unsealStoreValue(string $sealedValue): string
    {
        $data = json_decode($sealedValue);
        $ciphertext = base64_decode($data->ciphertext);
        $nonce = base64_decode($data->nonce);
        $identifier = $data->key ?? SecurityModule::SK_STORE_LEGACY;
        $key = $this->securityCache->getSecretKey($identifier);
        return sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
    }

    /**
     * Unseal value from the store using the general store encryption key.
     *
     * @param string|null $sealedValue Sealed value.
     *
     * @return string|null Unsealed value.
     *
     * @throws CacheEntryNotFoundException
     * @throws SodiumException
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
