<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Hasher;

use App\Services\SearchHash\Hasher\Hasher;
use App\Services\SearchHash\Hasher\NoOpHasher;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class NoOpHasherTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $hasher = new NoOpHasher();

        $this->assertInstanceOf(NoOpHasher::class, $hasher);
        $this->assertInstanceOf(Hasher::class, $hasher);
    }

    public function testHash(): void
    {
        $hasher = new NoOpHasher();

        $value = 'my_to_be_hashed_value';

        $this->assertSame($value, $hasher->hash($value));
    }
}
