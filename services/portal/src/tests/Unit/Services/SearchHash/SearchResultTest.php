<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash;

use App\Models\Eloquent\EloquentCase;
use App\Services\SearchHash\SearchResult;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\SearchHashResultType;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class SearchResultTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var EloquentCase&MockInterface $searchedModel */
        $searchedModel = Mockery::mock(EloquentCase::class);

        $searchHashResultType = SearchHashResultType::from('index');

        /** @var Collection&MockInterface $hashesByKey */
        $hashesByKey = Mockery::mock(Collection::class);

        $token = '';

        $this->assertInstanceOf(
            SearchResult::class,
            new SearchResult($searchedModel, $token, $searchHashResultType, $hashesByKey),
        );
    }
}
