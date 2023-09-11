<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Dto;

use Illuminate\Support\Collection;

final class SearchHashResult
{
    /**
     * @param Collection<int,SearchHashSourceResult> $sources
     */
    public function __construct(
        public readonly string $key,
        public readonly string $hash,
        public readonly Collection $sources,
    ) {
    }
}
