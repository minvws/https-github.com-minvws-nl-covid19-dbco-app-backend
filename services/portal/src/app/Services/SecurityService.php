<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeZone;
use DBCO\Shared\Application\Helpers\DateTimeHelper;
use Exception;
use Generator;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;
use MinVWS\DBCO\Encryption\Security\SecurityCache;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Encryption\Security\StorageTermUnit;
use Throwable;

use function sodium_crypto_box_publickey_from_secretkey;
use function sprintf;

/**
 * @codeCoverageIgnore
 */
class SecurityService
{
    /**
     * Mutations.
     */
    public const MUTATION_CREATED = 'CREATED';
    public const MUTATION_LOADED = 'LOADED';
    public const MUTATION_DELETED = 'DELETED';
    public const MUTATION_ERROR = 'ERROR';

    private const TERM_INTERVALS = [
        StorageTerm::VERY_SHORT => [
            'cleanUpInterval' => 1,
            'activeInterval' => 3,
        ],
        StorageTerm::SHORT => [
            'cleanUpInterval' => 7,
            'activeInterval' => 28,
        ],
        StorageTerm::LONG => [
            'cleanUpInterval' => 3,
            'activeInterval' => 60,
        ],
    ];

    public function __construct(
        public SecurityModule $securityModule,
        private SecurityCache $securityCache,
        private DateTimeHelper $dateTimeHelper,
        #[Config('securitymodule.store_key_time_zone')]
        private string $storeKeyTimeZone,
    ) {
    }

    private function cacheKey(string $identifier): void
    {
        $secretKey = $this->securityModule->getSecretKey($identifier);
        $this->securityCache->setSecretKey($identifier, $secretKey);
    }

    public function cacheKeys(bool $force = true): void
    {
        // check if there is already a key in the cache, in which case we assume
        // all the other keys are already cached as well (by the manage process)
        if (!$force && $this->securityCache->hasSecretKey(SecurityModule::SK_KEY_EXCHANGE)) {
            return;
        }

        foreach (SecurityModule::SK_DEFAULT_KEYS as $key) {
            $this->cacheKey($key);
        }

        $this->cacheKeysForStorageTerm(StorageTerm::veryShort());
        $this->cacheKeysForStorageTerm(StorageTerm::short());
        $this->cacheKeysForStorageTerm(StorageTerm::long());
    }

    private function cacheKeysForStorageTerm(StorageTerm $term): void
    {
        $activeInterval = self::TERM_INTERVALS[(string) $term]['activeInterval'];

        $timeZone = new DateTimeZone($this->storeKeyTimeZone);
        $currentUnit = $term->unitForDateTime($this->dateTimeHelper->now($timeZone));
        $oldestValidUnit = $currentUnit->sub($activeInterval);

        foreach ($this->iterateStorageTermUnits($oldestValidUnit, $currentUnit) as $unit) {
            $this->loadStorageTermUnit($unit);
        }
    }

    /**
     * @param bool $force Overwrite existing key (if it already exists).
     *
     * @return bool Key successfully created.
     */
    public function createKeyExchangeSecretKey(bool $force = false): bool
    {
        return $this->createKey(SecurityModule::SK_KEY_EXCHANGE, $force);
    }

    /**
     * @param string $identifier Key identifier.
     * @param bool $force Overwrite existing key (if it already exists).
     *
     * @return bool Key successfully created.
     */
    public function createKey(string $identifier, bool $force = false): bool
    {
        if ($force) {
            $this->securityModule->deleteSecretKey($identifier);
        } else {
            try {
                $this->securityModule->getSecretKey($identifier); // should not be successful
                return false;
            } catch (Throwable $e) {
                // ignore
            }
        }

        $secretKey = $this->securityModule->generateSecretKey($identifier);
        $this->securityCache->setSecretKey($identifier, $secretKey);
        return true;
    }

    public function getKeyExchangePublicKey(): ?string
    {
        return $this->getPublicKey(SecurityModule::SK_KEY_EXCHANGE);
    }

    /**
     * Returns the key exchange public key.
     */
    public function getPublicKey(string $identifier): ?string
    {
        try {
            $secretKey = $this->securityModule->getSecretKey($identifier);
            return sodium_crypto_box_publickey_from_secretkey($secretKey);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function identifierForStorageTermUnit(StorageTermUnit $unit): string
    {
        return sprintf(SecurityModule::SK_STORE_TEMPLATE, (string) $unit);
    }

    /**
     * Iterates over the given storage term period. Both first and last units are inclusive.
     *
     * @return Generator<StorageTermUnit>
     */
    private function iterateStorageTermUnits(StorageTermUnit $first, StorageTermUnit $last): Generator
    {
        $current = $first;

        while (true) {
            yield $current;

            if ($current->equals($last)) {
                break;
            }

            $current = $current->next();
        }
    }

    private function deleteStorageTermUnit(StorageTermUnit $unit, callable $mutationCallback): void
    {
        try {
            $identifier = $this->identifierForStorageTermUnit($unit);

            if (!$this->securityModule->hasSecretKey($identifier)) {
                return; // already deleted
            }

            // delete secret key from the security cache and module
            $this->securityCache->deleteSecretKey($identifier);
            $this->securityModule->deleteSecretKey($identifier);
            $mutationCallback($unit, self::MUTATION_DELETED);
        } catch (Throwable $e) {
            $mutationCallback($unit, self::MUTATION_ERROR, $e);
        }
    }

    private function createOrLoadStorageTermUnit(StorageTermUnit $unit, callable $mutationCallback, bool $reportAlreadyLoaded = false): void
    {
        try {
            $identifier = $this->identifierForStorageTermUnit($unit);

            if ($this->securityCache->hasSecretKey($identifier)) {
                if ($reportAlreadyLoaded) {
                    $mutationCallback($unit, self::MUTATION_LOADED);
                }

                return; // already in cache
            }

            $exists = $this->securityModule->hasSecretKey($identifier);
            $secretKey = $exists ? $this->securityModule->getSecretKey($identifier) : $this->securityModule->generateSecretKey($identifier);

            $this->securityCache->setSecretKey($identifier, $secretKey);
            $mutationCallback($unit, $exists ? self::MUTATION_LOADED : self::MUTATION_CREATED);
        } catch (Throwable $e) {
            $mutationCallback($unit, self::MUTATION_ERROR, $e);
        }
    }

    private function loadStorageTermUnit(StorageTermUnit $unit, ?callable $mutationCallback = null, bool $reportAlreadyLoaded = false): void
    {
        $mutationCallback = $mutationCallback ?? static fn () => null;

        try {
            $identifier = $this->identifierForStorageTermUnit($unit);

            if ($this->securityCache->hasSecretKey($identifier)) {
                if ($reportAlreadyLoaded) {
                    $mutationCallback($unit, self::MUTATION_LOADED);
                }

                return; // already in cache
            }

            if (!$this->securityModule->hasSecretKey($identifier)) {
                return; // doesn't exist
            }

            // store the key in the cache
            $secretKey = $this->securityModule->getSecretKey($identifier);
            $this->securityCache->setSecretKey($identifier, $secretKey);
            $mutationCallback($unit, self::MUTATION_LOADED);
        } catch (Throwable $e) {
            $mutationCallback($unit, self::MUTATION_ERROR, $e);
        }
    }

    /**
     * @param callable $mutationCallback Mutation callback, will be called for every mutation result.
     * @param StorageTerm $term Storage term.
     * @param StorageTermUnit|null $previousCurrentUnit Previous current unit.
     * @param bool $createMissingPastKeys Create missing keys in the past.
     *
     * @return StorageTermUnit Current unit.
     *
     * @throws Exception
     */
    public function manageStoreSecretKeys(StorageTerm $term, ?StorageTermUnit $previousCurrentUnit, callable $mutationCallback, bool $createMissingPastKeys = false): StorageTermUnit
    {
        $intervals = self::TERM_INTERVALS[(string) $term];
        $activeInterval = $intervals['activeInterval'];
        $cleanUpInterval = $intervals['cleanUpInterval'];

        $timeZone = new DateTimeZone($this->storeKeyTimeZone);
        $currentUnit = $term->unitForDateTime($this->dateTimeHelper->now($timeZone));

        if ($previousCurrentUnit !== null && $currentUnit->equals($previousCurrentUnit)) {
            return $previousCurrentUnit; // nothing to do
        }

        $reportAlreadyLoaded = $previousCurrentUnit === null;

        $oldestValidUnit = $currentUnit->sub($activeInterval);
        $oldestExpiredUnit = $oldestValidUnit->sub($cleanUpInterval);
        $newestExpiredUnit = $oldestValidUnit->previous();

        // delete expired units
        foreach ($this->iterateStorageTermUnits($oldestExpiredUnit, $newestExpiredUnit) as $unit) {
            $this->deleteStorageTermUnit($unit, $mutationCallback);
        }

        // load active units
        foreach ($this->iterateStorageTermUnits($oldestValidUnit, $currentUnit->previous()) as $unit) {
            if ($createMissingPastKeys) {
                $this->createOrloadStorageTermUnit($unit, $mutationCallback, $reportAlreadyLoaded);
            } else {
                $this->loadStorageTermUnit($unit, $mutationCallback, $reportAlreadyLoaded);
            }
        }

        // create or load current unit
        $this->createOrLoadStorageTermUnit($currentUnit, $mutationCallback, $reportAlreadyLoaded);

        // create or load next unit; create early so it is already available after midnight
        $this->createOrLoadStorageTermUnit($currentUnit->next(), $mutationCallback, $reportAlreadyLoaded);

        return $currentUnit;
    }
}
