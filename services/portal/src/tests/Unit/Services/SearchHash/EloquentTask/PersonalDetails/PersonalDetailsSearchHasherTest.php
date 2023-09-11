<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\EloquentTask\PersonalDetails;

use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsHash;
use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsSearchHasher;
use App\Services\SearchHash\Hasher\NoOpHasher;
use App\Services\SearchHash\Normalizer\NoOpNormalizer;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
class PersonalDetailsSearchHasherTest extends UnitTestCase
{
    protected PersonalDetailsSearchHasher $hasher;

    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(
            PersonalDetailsSearchHasher::class,
            new PersonalDetailsSearchHasher(new NoOpNormalizer(), new NoOpHasher(), new stdClass()),
        );
    }

    public function testGetBsnHash(): void
    {
        $hasher = new PersonalDetailsSearchHasher(new NoOpNormalizer(), new NoOpHasher(), $this->getIndexHashFixture());

        $this->assertSame(
            '123#20000401',
            $hasher->getBsnHash(),
            'The hash value is not composed the same as expected',
        );
    }

    public function testGetAddressHash(): void
    {
        $hasher = new PersonalDetailsSearchHasher(new NoOpNormalizer(), new NoOpHasher(), $this->getIndexHashFixture());

        $this->assertSame(
            '01#20000401#9999XX#A',
            $hasher->getAddressHash(),
            'The hash value is not composed the same as expected',
        );
    }

    protected function getIndexHashFixture(): PersonalDetailsHash
    {
        return new PersonalDetailsHash(
            dateOfBirth: CarbonImmutable::createFromFormat('Y-m-d', '2000-04-01'),
            lastThreeBsnDigits: '123',
            postalCode: '9999XX',
            houseNumber: '01',
            houseNumberSuffix: 'A',
        );
    }
}
