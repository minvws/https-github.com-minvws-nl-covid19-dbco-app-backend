<?php

declare(strict_types=1);

namespace App\Services\SearchHash\EloquentTask\PersonalDetails;

use App\Models\Eloquent\EloquentTask;
use App\Services\SearchHash\Attribute\HashSource;
use App\Services\SearchHash\SafeIssetFragment;
use DateTimeInterface;

use function substr;

class PersonalDetailsHash
{
    use SafeIssetFragment;

    public function __construct(
        #[HashSource('personalDetails.dateOfBirth')]
        public readonly ?DateTimeInterface $dateOfBirth,
        #[HashSource('personalDetails.bsnCensored')]
        public readonly ?string $lastThreeBsnDigits,
        #[HashSource('personalDetails.address.postalCode')]
        public readonly ?string $postalCode,
        #[HashSource('personalDetails.address.houseNumber')]
        public readonly ?string $houseNumber,
        #[HashSource('personalDetails.address.houseNumberSuffix')]
        public readonly ?string $houseNumberSuffix,
    ) {
    }

    public function isOptional(string $propertyKey): bool
    {
        return $propertyKey === 'houseNumberSuffix';
    }

    public static function fromTask(EloquentTask $task): self
    {
        if (self::issetFragment($task, 'personal_details')) {
            $personalDetails = $task->personal_details;

            return new PersonalDetailsHash(
                dateOfBirth: $personalDetails->dateOfBirth,
                lastThreeBsnDigits: $personalDetails->bsnCensored === null
                    ? null
                    : substr($personalDetails->bsnCensored, -3),
                postalCode: $personalDetails->address?->postalCode,
                houseNumber: $personalDetails->address?->houseNumber,
                houseNumberSuffix: $personalDetails->address?->houseNumberSuffix,
            );
        }

        return new PersonalDetailsHash(
            dateOfBirth: null,
            lastThreeBsnDigits: null,
            postalCode: null,
            houseNumber: null,
            houseNumberSuffix: null,
        );
    }
}
