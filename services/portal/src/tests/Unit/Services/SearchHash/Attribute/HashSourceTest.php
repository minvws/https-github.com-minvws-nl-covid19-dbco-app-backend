<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Attribute;

use App\Services\SearchHash\Attribute\HashSource;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
class HashSourceTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(HashSource::class, new HashSource(''));
    }
}
