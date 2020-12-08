<?php
namespace DBCO\HealthAuthorityAPI\Application\Services;

use DBCO\HealthAuthorityAPI\Application\Security\SecurityModule;
use DBCO\HealthAuthorityAPI\Application\Security\SecurityCache;

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
    private SecurityCache $securityModuleCache;

    /**
     * Constructor.
     */
    public function __construct(SecurityModule $securityModule, SecurityCache $securityModuleCache)
    {
        $this->securityModule = $securityModule;
        $this->securityModuleCache = $securityModuleCache;
    }

    /**
     * Load key with the given identifier.
     *
     * @param string $identifier
     */
    private function loadKey(string $identifier)
    {
        $secretKey = $this->securityModule->getSecretKey($identifier);
        $this->securityModuleCache->setSecretKey($identifier, $secretKey);
    }
    
    /**
     * Load all keys in the cache.
     */
    public function loadKeys(): void
    {
        $this->loadKey(SecurityModule::SK_KEY_EXCHANGE);
        $this->loadKey(SecurityModule::SK_STORE);
    }
}
