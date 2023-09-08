<?php

declare(strict_types=1);

namespace App\Dto\Chore;

readonly class Resource
{
    public function __construct(
        public string $type,
        public string $id,
    ) {
    }
}
