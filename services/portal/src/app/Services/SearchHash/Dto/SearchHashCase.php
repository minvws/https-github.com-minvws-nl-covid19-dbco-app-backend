<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Dto;

final class SearchHashCase
{
    public readonly bool $hasHashes;

    public function __construct(
        public readonly string $uuid,
        public readonly string $updatedAt,
        int $hasHashes,
    ) {
        $this->hasHashes = $hasHashes === 1;
    }
}
