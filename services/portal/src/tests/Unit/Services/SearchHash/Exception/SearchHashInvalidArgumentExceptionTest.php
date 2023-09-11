<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Exception;

use App\Services\SearchHash\Exception\SearchHashException;
use App\Services\SearchHash\Exception\SearchHashInvalidArgumentException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
class SearchHashInvalidArgumentExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new SearchHashInvalidArgumentException();

        $this->assertInstanceOf(SearchHashInvalidArgumentException::class, $e);
        $this->assertInstanceOf(SearchHashException::class, $e);
        $this->assertInstanceOf(InvalidArgumentException::class, $e);
    }
}
