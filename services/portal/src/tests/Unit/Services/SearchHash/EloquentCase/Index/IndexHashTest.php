<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\EloquentCase\Index;

use App\Services\SearchHash\EloquentCase\Index\IndexHash;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class IndexHashTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var DateTimeImmutable&MockInterface $dateOfBirth */
        $dateOfBirth = Mockery::mock(DateTimeImmutable::class);

        $this->assertInstanceOf(
            IndexHash::class,
            new IndexHash(
                dateOfBirth: $dateOfBirth,
                lastname: '',
                lastThreeBsnDigits: null,
                postalCode: '',
                houseNumber: '',
                houseNumberSuffix: null,
            ),
        );
    }
}
