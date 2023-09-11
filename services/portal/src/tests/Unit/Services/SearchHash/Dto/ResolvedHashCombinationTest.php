<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Dto;

use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\Dto\ResolvedHashCombination;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class ResolvedHashCombinationTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var HashCombination&MockInterface $hashCombination */
        $hashCombination = Mockery::mock(HashCombination::class);

        $this->assertInstanceOf(ResolvedHashCombination::class, new ResolvedHashCombination('', $hashCombination));
    }
}
