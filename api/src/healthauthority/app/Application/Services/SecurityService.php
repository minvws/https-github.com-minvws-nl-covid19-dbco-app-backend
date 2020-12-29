<?php
namespace DBCO\HealthAuthorityAPI\Application\Services;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use DBCO\HealthAuthorityAPI\Application\Security\SecurityModule;
use DBCO\HealthAuthorityAPI\Application\Security\SecurityCache;
use DBCO\Shared\Application\Helpers\DateTimeHelper;
use Exception;

/**
 * Security service.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Services
 */
class SecurityService
{
    /**
     * Mutations.
     */
    public const MUTATION_CREATED = 'CREATED';
    public const MUTATION_LOADED  = 'LOADED';
    public const MUTATION_DELETED = 'DELETED';
    public const MUTATION_ERROR   = 'ERROR';

    /**
     * Number of days we go back cleaning up expired keys.
     */
    private const CLEANUP_DAYS = 7;

    /**
     * @var SecurityModule
     */
    private SecurityModule $securityModule;

    /**
     * @var SecurityCache
     */
    private SecurityCache $securityCache;

    /**
     * @var DateTimeHelper
     */
    private DateTimeHelper $dateTimeHelper;

    /**
     * @var string
     */
    private string $storeKeyTimeZone;

    /**
     * @var int
     */
    private int $storeKeyMaxDays;

    /**
     * Constructor.
     *
     * @param SecurityModule $securityModule
     * @param SecurityCache  $securityCache
     * @param DateTimeHelper $dateTimeHelper
     * @param string         $storeKeyTimeZone
     * @param int            $storeKeyMaxDays
     */
    public function __construct(SecurityModule $securityModule,
                                SecurityCache $securityCache,
                                DateTimeHelper $dateTimeHelper,
                                string $storeKeyTimeZone,
                                int $storeKeyMaxDays)
    {
        $this->securityModule = $securityModule;
        $this->securityCache = $securityCache;
        $this->dateTimeHelper = $dateTimeHelper;
        $this->storeKeyTimeZone = $storeKeyTimeZone;
        $this->storeKeyMaxDays = $storeKeyMaxDays;
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

        // TODO: delete
        $this->cacheKey(SecurityModule::SK_STORE_LEGACY);

        $timeZone = new DateTimeZone($this->storeKeyTimeZone);
        $today = $this->dateTimeHelper->now($timeZone);

        $day = $today->sub(new DateInterval('P' . ($this->storeKeyMaxDays - 1) . 'D'));
        while ($day->format('Ymd') <= $today->format('Ymd')) {
            $identifier = sprintf(SecurityModule::SK_STORE_TEMPLATE, $day->format('Ymd'));
            $this->cacheKey($identifier);
        }

        $todayIdentifier = sprintf(SecurityModule::SK_STORE_TEMPLATE, $today->format('Ymd'));
        $this->securityCache->setValue(SecurityCache::SK_STORE_CURRENT_IDENTIFIER, $todayIdentifier);
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
     * Delete the store secret key with the given identifier.
     *
     * @param string $identifier
     *
     * @return bool
     */
    private function deleteStoreSecretKey(string $identifier): bool
    {
        if (!$this->securityModule->hasSecretKey($identifier)) {
            return false; // already deleted
        }

        // delete secret key from the security cache and module
        $this->securityCache->deleteSecretKey($identifier);
        $this->securityModule->deleteSecretKey($identifier);

        return true;
    }

    /**
     * Create or load the store secret key with the given identifier.
     *
     * @param string $identifier
     *
     * @return bool
     */
    private function createOrLoadStoreSecretKey(string $identifier): bool
    {
        $exists = $this->securityModule->hasSecretKey($identifier);

        if ($exists) {
            // load existing key
            $secretKey = $this->securityModule->getSecretKey($identifier);
        } else {
            // create new key
            $secretKey = $this->securityModule->generateSecretKey($identifier);
        }

        // store the key in the cache
        $this->securityCache->setSecretKey($identifier, $secretKey);

        return !$exists;
    }

    /**
     * Manage / rotate store secret keys.
     *
     * @param callable               $mutationCallback   Mutation callback, will be called for every mutation result.
     * @param DateTimeInterface|null $previousCurrentDay Previous current day.
     *
     * @return DateTimeInterface Current day (for current key).
     *
     * @throws Exception
     */
    public function manageStoreSecretKeys(callable $mutationCallback, ?DateTimeInterface $previousCurrentDay): DateTimeInterface
    {
        $timeZone = new DateTimeZone($this->storeKeyTimeZone);
        $today = $this->dateTimeHelper->now($timeZone);

        if ($previousCurrentDay !== null &&
            $today->format('Ymd') === $previousCurrentDay->format('Ymd')) {
            return $today; // nothing to do
        }

        $firstValidDay = $today->sub(new DateInterval('P' . ($this->storeKeyMaxDays - 1) . 'D'));
        $day = $firstValidDay->sub(new DateInterval('P' . self::CLEANUP_DAYS . 'D'));
        while ($day->format('Ymd') <= $today->format('Ymd')) {
            $identifier = sprintf(SecurityModule::SK_STORE_TEMPLATE, $day->format('Ymd'));

            try {
                if ($day->format('Ymd') < $firstValidDay->format('Ymd')) {
                    // expired day, delete from security module
                    $deleted = $this->deleteStoreSecretKey($identifier);

                    if ($deleted) {
                        $mutationCallback($day, self::MUTATION_DELETED);
                    }
                } else {
                    // create or load valid day key
                    $created = $this->createOrLoadStoreSecretKey($identifier);

                    if ($created || $previousCurrentDay === null) {
                        $mutationCallback($day, $created ? self::MUTATION_CREATED : self::MUTATION_LOADED);
                    }
                }
            } catch (Exception $e) {
                $mutationCallback($day, self::MUTATION_ERROR, $e);
            }

            $day = $day->add(new DateInterval('P1D'));
        }

        $todayIdentifier = sprintf(SecurityModule::SK_STORE_TEMPLATE, $today->format('Ymd'));
        $this->securityCache->setValue(SecurityCache::SK_STORE_CURRENT_IDENTIFIER, $todayIdentifier);

        // TODO: delete the legacy store secret key
        //$this->securityModule->deleteSecretKey(SecurityModule::SK_STORE_LEGACY);

        return $today;
    }
}
