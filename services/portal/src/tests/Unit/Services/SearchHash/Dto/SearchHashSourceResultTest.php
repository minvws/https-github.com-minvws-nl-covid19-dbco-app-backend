<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Dto;

use App\Services\SearchHash\Dto\SearchHashSourceResult;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class SearchHashSourceResultTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(SearchHashSourceResult::class, new SearchHashSourceResult(
            valueObjectKey: 'my_value_object_key',
            valueObjectValue: 'my_value_object_value',
            sourceKey: 'my_source_key',
        ));
    }
}
