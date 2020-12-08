<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

/**
 * Wraps a security module and adds a cache for the secret keys.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class CachedSecurityModule implements SecurityModule
{
    /**
     * @var SecurityModule
     */
    private SecurityModule $securityModule;

    /**
     * @var SecurityModuleCache
     */
    private SecurityModuleCache $cache;

    /**
     * Constructor.
     *
     * @param SecurityModule           $securityModule Security module that needs to be wrapped.
     * @param SecurityModuleCache|null $cache          Cache backend. Defaults to an InstanceSecurityModuleCache backend.
     */
    public function __construct(SecurityModule $securityModule, ?SecurityModuleCache $cache = null)
    {
        $this->securityModule = $securityModule;
        $this->cache = $cache ?? new InstanceSecurityModuleCache();
    }

    /**
     * @inheritdoc
     */
    public function generateSecretKey(string $identifier): string
    {
        $secretKey = $this->securityModule->generateSecretKey($identifier);
        $this->cache->setSecretKey($identifier, $secretKey);
        return $secretKey;
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): string
    {
        if ($this->cache->hasSecretKey($identifier)) {
            return $this->cache->getSecretKey($identifier);
        } else {
            $secretKey = $this->securityModule->getSecretKey($identifier);
            $this->cache->setSecretKey($identifier, $secretKey);
            return $secretKey;
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteSecretKey(string $identifier): void
    {
        $this->securityModule->deleteSecretKey($identifier);
        $this->cache->deleteSecretKey($identifier);
    }

    /**
     * @inheritdoc
     */
    public function renameSecretKey(string $oldIdentifier, string $newIdentifier)
    {
        $this->securityModule->renameSecretKey($oldIdentifier, $newIdentifier);

        if ($this->cache->hasSecretKey($oldIdentifier)) {
            $secretKey = $this->cache->getSecretKey($oldIdentifier);
            $this->cache->deleteSecretKey($oldIdentifier);
            $this->cache->setSecretKey($newIdentifier, $secretKey);
        }
    }

    /**
     * @inheritdoc
     */
    public function randomBytes(int $length): string
    {
        return $this->securityModule->randomBytes($length);
    }
}
