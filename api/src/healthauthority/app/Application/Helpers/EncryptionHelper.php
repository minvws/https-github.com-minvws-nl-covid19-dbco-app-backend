<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Helpers;

use DBCO\HealthAuthorityAPI\Application\Security\SecurityModule;
use DBCO\Shared\Application\Models\SealedData;

/**
 * Utility methods for encryption.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Helpers
 */
class EncryptionHelper
{
    /**
     * @var SecurityModule
     */
    private SecurityModule $securityModule;

    /**
     * Constructor.
     *
     * @param SecurityModule $securityModule
     */
    public function __construct(SecurityModule $securityModule)
    {
        $this->securityModule = $securityModule;
    }

    /**
     * Create a key pair.
     *
     * @return string
     */
    public function createHealthAuthorityKeyPair(): string
    {
        $seed = $this->securityModule->randomBytes(SODIUM_CRYPTO_KX_SEEDBYTES);
        return sodium_crypto_kx_seed_keypair($seed);
    }

    /**
     * Returns the public key for the given key pair.
     *
     * @param string $keyPair
     *
     * @return string
     */
    public function getHealthAuthorityPublicKey(string $keyPair): string
    {
        return sodium_crypto_kx_publickey($keyPair);
    }

    /**
     * Returns the secret key for the given key pair.
     *
     * @param string $keyPair
     *
     * @return string
     */
    public function getHealthAuthoritySecretKey(string $keyPair): string
    {
        return sodium_crypto_kx_secretkey($keyPair);
    }

    /**
     * Unseal client public key using the general health authority key pair.
     *
     * @param string $sealedClientPublicKey
     *
     * @return string Unsealed client public key.
     */
    public function unsealClientPublicKey(string $sealedClientPublicKey): string
    {
        // unseal the client public key using the general health authority key exchange key
        $secretKey = $this->securityModule->getSecretKey(SecurityModule::SK_KEY_EXCHANGE);
        $publicKey = sodium_crypto_box_publickey_from_secretkey($secretKey);
        $keyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey($secretKey, $publicKey);
        return sodium_crypto_box_seal_open($sealedClientPublicKey, $keyPair);
    }

    /**
     * Derive shared secret keys for the given case key pair and client public key.
     *
     * @param string $caseKeyPair
     * @param string $clientPublicKey
     *
     * @return array Array containing rx and tx shared secret keys.
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
     * @param array $secretKeys
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
     * @param string $healthAuthorityPublicKey
     * @param string $clientPublicKey
     *
     * @return string Sealed public key.
     */
    public function sealHealthAuthorityPublicKeyForClient(string $healthAuthorityPublicKey, string $clientPublicKey): string
    {
        return sodium_crypto_box_seal($healthAuthorityPublicKey, $clientPublicKey);
    }

    /**
     * Seal message for client.
     *
     * @param string $message
     * @param string $transferKey
     *
     * @return SealedData
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
     * @param SealedData $sealedMessage
     * @param string $receiveKey
     *
     * @return string
     */
    public function unsealMessageFromClient(SealedData $sealedMessage, string $receiveKey): string
    {
        return sodium_crypto_secretbox_open($sealedMessage->ciphertext, $sealedMessage->nonce, $receiveKey);
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
        return $this->sealStoreValueWithKey($value, SecurityModule::SK_STORE);
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
        $nonce = $this->securityModule->randomBytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key = $this->securityModule->getSecretKey($secretKeyIdentifier);
        $ciphertext = sodium_crypto_secretbox($value, $nonce, $key);
        return json_encode([
            'ciphertext' => base64_encode($ciphertext),
            'nonce' => base64_encode($nonce)
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
        $key = $this->securityModule->getSecretKey(SecurityModule::SK_STORE);
        return sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
    }

    /**
     * Unseal value from the store using the current store key and seal
     * with the registered new store key.
     *
     * @param string $sealedValue
     *
     * @return string Sealed value with the new store key.
     */
    public function resealStoreValue(string $sealedValue): string
    {
        $value = $this->unsealStoreValue($sealedValue);
        return $this->sealStoreValueWithKey($value, SecurityModule::SK_STORE_NEW);
    }
}
