<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Dto;

final class SearchHashSourceResult
{
    public function __construct(
        public readonly string $valueObjectKey,
        public readonly object|string|int|float|bool|null $valueObjectValue,
        public readonly string $sourceKey,
    ) {
    }
}
