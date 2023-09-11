<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\EloquentTask\PersonalDetails;

use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsHash;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
class PersonalDetailsHashTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var DateTimeImmutable&MockInterface $dateOfBirth */
        $dateOfBirth = Mockery::mock(DateTimeImmutable::class);

        $this->assertInstanceOf(
            PersonalDetailsHash::class,
            new PersonalDetailsHash(
                dateOfBirth: $dateOfBirth,
                lastThreeBsnDigits: null,
                postalCode: '',
                houseNumber: '',
                houseNumberSuffix: null,
            ),
        );
    }
}
