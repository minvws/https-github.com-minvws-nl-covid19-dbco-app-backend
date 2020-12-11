<?php
namespace DBCO\HealthAuthorityAPI\Application\Services;

use DBCO\HealthAuthorityAPI\Application\Security\SecurityModule;
use DBCO\HealthAuthorityAPI\Application\Security\SecurityCache;
use Exception;
use SodiumException;

/**
 * Security service.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Services
 */
class SecurityService
{
    /**
     * @var SecurityModule
     */
    private SecurityModule $securityModule;

    /**
     * @var SecurityCache
     */
    private SecurityCache $securityCache;

    /**
     * Constructor.
     */
    public function __construct(SecurityModule $securityModule, SecurityCache $securityCache)
    {
        $this->securityModule = $securityModule;
        $this->securityCache = $securityCache;
    }

    /**
     * Load key with the given identifier.
     *
     * @param string $identifier
     */
    private function cacheKey(string $identifier)
    {
        $secretKey = $this->securityModule->getSecretKey($identifier);
        $this->securityCache->setSecretKey($identifier, $secretKey);
    }
    
    /**
     * Cache all security module keys.
     */
    public function cacheKeys(): void
    {
        $this->cacheKey(SecurityModule::SK_KEY_EXCHANGE);
        $this->cacheKey(SecurityModule::SK_STORE);
    }

    /**
     * Create key exchange key.
     *
     * @param bool $force Overwrite existing key (if it already exists).
     *
     * @return bool Key successfully created.
     */
    public function createKeyExchangeSecretKey(bool $force = false): bool
    {
        if ($force) {
            $this->securityModule->deleteSecretKey(SecurityModule::SK_KEY_EXCHANGE);
        } else {
            try {
                $this->securityModule->getSecretKey(SecurityModule::SK_KEY_EXCHANGE); // should not be successful
                return false;
            } catch (Exception $e) {}
        }

        $secretKey = $this->securityModule->generateSecretKey(SecurityModule::SK_KEY_EXCHANGE);
        $this->securityCache->setSecretKey(SecurityModule::SK_KEY_EXCHANGE, $secretKey);
        return true;
    }

    /**
     * Returns the key exchange public key.
     *
     * @return string
     */
    public function getKeyExchangePublicKey(): ?string
    {
        try {
            $secretKey = $this->securityModule->getSecretKey(SecurityModule::SK_KEY_EXCHANGE);
            return sodium_crypto_box_publickey_from_secretkey($secretKey);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create key exchange key.
     *
     * @param bool $force Overwrite existing key (if it already exists).
     *
     * @return bool Result.
     */
    public function createStoreSecretKey(bool $force = false): bool
    {
        if ($force) {
            $this->securityModule->deleteSecretKey(SecurityModule::SK_STORE);
        } else {
            try {
                $this->securityModule->getSecretKey(SecurityModule::SK_STORE); // should not be successful
                return false;
            } catch (Exception $e) {}
        }

        $secretKey = $this->securityModule->generateSecretKey(SecurityModule::SK_STORE);
        $this->securityCache->setSecretKey(SecurityModule::SK_STORE, $secretKey);
        return true;
    }
}
