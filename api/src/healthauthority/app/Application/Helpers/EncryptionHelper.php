<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Helpers;

use DBCO\Shared\Application\Models\SealedMessage;

/**
 * Utility methods for encryption.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Helpers
 */
class EncryptionHelper
{
    /**
     * @var string
     */
    private string $generalKeyPair;

    /**
     * Constructor.
     *
     * @param string $generalKeyPair
     */
    public function __construct(string $generalKeyPair)
    {
        $this->generalKeyPair = base64_decode($generalKeyPair);
    }

    /**
     * Create a key pair.
     *
     * @return string
     */
    public function createHealthAuthorityKeyPair(): string
    {
        return sodium_crypto_kx_keypair();
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
        // unseal the client public key using the general health authority key pair
        return sodium_crypto_box_seal_open($sealedClientPublicKey, $this->generalKeyPair);
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
     * Seal health authority public key using the client public ikey.
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
     * @return SealedMessage
     */
    public function sealMessageForClient(string $message, string $transferKey): SealedMessage
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($message, $nonce, $transferKey);
        return new SealedMessage($ciphertext, $nonce);
    }

    /**
     * Unseal message from client.
     *
     * @param SealedMessage $sealedMessage
     * @param string        $receiveKey
     *
     * @return string
     */
    public function unsealMessageFromClient(SealedMessage $sealedMessage, string $receiveKey): string
    {
        return sodium_crypto_secretbox_open($sealedMessage->ciphertext, $sealedMessage->nonce, $receiveKey);
    }
}
