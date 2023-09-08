<?php

declare(strict_types=1);

namespace App\Services\SearchHash;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Services\SearchHash\Dto\SearchHashResult;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\SearchHashResultType;

final class SearchResult
{
    /**
     * @param Collection<int,SearchHashResult> $hashesByKey
     */
    public function __construct(
        public readonly EloquentCase|EloquentTask $searchedModel,
        public readonly string $token,
        public readonly SearchHashResultType $searchHashResultType,
        public readonly Collection $hashesByKey,
    ) {
    }
}
