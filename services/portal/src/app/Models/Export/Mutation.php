<?php

declare(strict_types=1);

namespace App\Models\Export;

use DateTimeInterface;

class Mutation
{
    public function __construct(
        public readonly string $id,
        public readonly DateTimeInterface $updatedAt,
        public readonly ?DateTimeInterface $deletedAt,
    ) {
    }
}
