<?php

declare(strict_types=1);

namespace Tests\Mocks;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Encryption\Security\CacheEntryNotFoundException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

use function base64_decode;
use function base64_encode;
use function json_decode;
use function json_encode;

class MockEncryptionHelper extends EncryptionHelper
{
    private function validateReferenceDateTime(StorageTerm $storageTerm, DateTimeInterface $referenceDateTime): void
    {
        if (!$this->hasStoreKey($storageTerm, $referenceDateTime)) {
            throw new CacheEntryNotFoundException();
        }
    }

    public function hasStoreKey(StorageTerm $storageTerm, DateTimeInterface $referenceDateTime): bool
    {
        return $storageTerm->expirationDateForReferenceDate($referenceDateTime) > CarbonImmutable::now();
    }

    public function sealStoreValue(string $value, StorageTerm $storageTerm, DateTimeInterface $referenceDateTime): string
    {
        $this->validateReferenceDateTime($storageTerm, $referenceDateTime);

        return json_encode([
            'value' => base64_encode($value),
            'storageTerm' => (string) $storageTerm,
            'referenceDateTime' => $referenceDateTime->format('Y-m-d'),
        ]);
    }

    public function sealStoreValueWithKey(string $value, string $secretKeyIdentifier): string
    {
        return json_encode([
            'value' => base64_encode($value),
            'secretKeyIdentifier' => $secretKeyIdentifier,
        ]);
    }

    public function unsealStoreValue(string $sealedValue): string
    {
        $data = json_decode($sealedValue);

        if (isset($data->referenceDateTime) && isset($data->storageTerm)) {
            $referenceDateTime = CarbonImmutable::parse($data->referenceDateTime);
            $this->validateReferenceDateTime(StorageTerm::forValue($data->storageTerm), $referenceDateTime);
        }

        $unsealedStoreValue = base64_decode($data->value, true);

        if ($unsealedStoreValue === false) {
            throw new CacheEntryNotFoundException('unable to decode value');
        }

        return $unsealedStoreValue;
    }
}
