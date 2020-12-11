<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

use RuntimeException;

/**
 * Security module that uses injected keys, doesn't support key rollover
 * and uses PHP's built-in random bytes generator.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class SimpleSecurityModule implements SecurityModule
{
    /**
     * @var string
     */
    private string $keyExchangeSecretKey;

    /**
     * @var string
     */
    private string $storeSecretKey;

    /**
     * Constructor.
     *
     * @param string $keyExchangeSecretKey
     * @param string $storeSecretKey
     */
    public function __construct(string $keyExchangeSecretKey, string $storeSecretKey)
    {
        $this->keyExchangeSecretKey = $keyExchangeSecretKey;
        $this->storeSecretKey = $storeSecretKey;
    }

    /**
     * @inheritdoc
     */
    public function generateSecretKey(string $identifier): string
    {
        if ($identifier === self::SK_STORE_NEW) {
            // return the current store secret key, as we don't really replace the store secret key
            return $this->storeSecretKey;
        } else {
            throw new RuntimeException('Unsupported key identifier "' . $identifier . '"');
        }
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): string
    {
        if ($identifier === self::SK_KEY_EXCHANGE) {
            return $this->keyExchangeSecretKey;
        } else if ($identifier === self::SK_STORE) {
            return $this->storeSecretKey;
        } else if ($identifier === self::SK_STORE_NEW) {
            return $this->storeSecretKey;
        } else {
            throw new RuntimeException('Unsupported key identifier "' . $identifier . '"');
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteSecretKey(string $identifier): void
    {
        if ($identifier === self::SK_STORE) {
            // don't do anything, as we don't really replace the store secret key
        } else {
            throw new RuntimeException('Unsupported key identifier "' . $identifier . '"');
        }
    }

    /**
     * @inheritdoc
     */
    public function randomBytes(int $length): string
    {
        return random_bytes($length);
    }
}
