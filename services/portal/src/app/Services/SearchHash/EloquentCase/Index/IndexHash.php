<?php

declare(strict_types=1);

namespace App\Services\SearchHash\EloquentCase\Index;

use App\Models\CovidCase\Index;
use App\Services\SearchHash\Attribute\HashSource;
use DateTimeInterface;

use function is_null;
use function substr;

final class IndexHash
{
    public function __construct(
        #[HashSource('index.dateOfBirth')]
        public readonly ?DateTimeInterface $dateOfBirth,
        #[HashSource('index.lastname')]
        public readonly ?string $lastname,
        #[HashSource('index.bsnCensored')]
        public readonly ?string $lastThreeBsnDigits,
        #[HashSource('index.address.postalCode')]
        public readonly ?string $postalCode,
        #[HashSource('index.address.houseNumber')]
        public readonly ?string $houseNumber,
        #[HashSource('index.address.houseNumberSuffix')]
        public readonly ?string $houseNumberSuffix,
    ) {
    }

    public function isOptional(string $propertyKey): bool
    {
        return $propertyKey === 'houseNumberSuffix';
    }

    public static function fromIndex(Index $index): self
    {
        $address = $index->address ?? null;

        return new IndexHash(
            dateOfBirth: $index->dateOfBirth,
            lastname: $index->lastname,
            lastThreeBsnDigits: is_null($index->bsnCensored) ? null : substr($index->bsnCensored, -3),
            postalCode: $address?->postalCode,
            houseNumber: $address?->houseNumber,
            houseNumberSuffix: $address?->houseNumberSuffix,
        );
    }
}
