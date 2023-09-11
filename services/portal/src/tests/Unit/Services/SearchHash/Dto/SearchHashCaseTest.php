<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Dto;

use App\Services\SearchHash\Dto\SearchHashCase;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class SearchHashCaseTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(SearchHashCase::class, new SearchHashCase('', '', 1));
    }
}
