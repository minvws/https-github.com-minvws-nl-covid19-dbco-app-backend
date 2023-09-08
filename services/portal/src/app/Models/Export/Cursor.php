<?php

declare(strict_types=1);

namespace App\Models\Export;

use DateTimeInterface;

class Cursor
{
    public function __construct(
        public readonly DateTimeInterface $since,
        public readonly ?DateTimeInterface $until,
        public readonly ?string $id,
    ) {
    }
}
