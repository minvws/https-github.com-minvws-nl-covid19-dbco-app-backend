<?php

declare(strict_types=1);

namespace App\Repositories\Bsn\Dto;

use DateTimeInterface;

class PseudoBsnLookup
{
    public function __construct(
        public readonly DateTimeInterface $dateOfBirth,
        public readonly string $postalCode,
        public readonly string $houseNumber,
        public readonly ?string $houseNumberSuffix = null,
    ) {
    }
}
