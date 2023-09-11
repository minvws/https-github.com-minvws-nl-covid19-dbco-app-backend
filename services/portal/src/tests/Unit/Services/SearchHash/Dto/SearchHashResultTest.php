<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Dto;

use App\Services\SearchHash\Dto\SearchHashResult;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class SearchHashResultTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var Collection&MockInterface $sources */
        $sources = Mockery::mock(Collection::class);

        $this->assertInstanceOf(SearchHashResult::class, new SearchHashResult(
            key: 'my_key',
            hash: 'my_hash',
            sources: $sources,
        ));
    }
}
