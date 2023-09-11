<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Dto;

use App\Services\SearchHash\Attribute\HashCombination;

final class ResolvedHashCombination
{
    public function __construct(
        public readonly string $hashMethodName,
        public readonly HashCombination $hashCombination,
    ) {
    }
}
