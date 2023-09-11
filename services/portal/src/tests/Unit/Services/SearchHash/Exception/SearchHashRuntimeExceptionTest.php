<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Exception;

use App\Services\SearchHash\Exception\SearchHashException;
use App\Services\SearchHash\Exception\SearchHashRuntimeException;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
class SearchHashRuntimeExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new SearchHashRuntimeException();

        $this->assertInstanceOf(SearchHashRuntimeException::class, $e);
        $this->assertInstanceOf(SearchHashException::class, $e);
        $this->assertInstanceOf(RuntimeException::class, $e);
    }
}
