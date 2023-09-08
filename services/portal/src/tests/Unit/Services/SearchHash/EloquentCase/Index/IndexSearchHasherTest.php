<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\EloquentCase\Index;

use App\Services\SearchHash\EloquentCase\Index\IndexHash;
use App\Services\SearchHash\EloquentCase\Index\IndexSearchHasher;
use App\Services\SearchHash\Hasher\NoOpHasher;
use App\Services\SearchHash\Normalizer\NoOpNormalizer;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class IndexSearchHasherTest extends UnitTestCase
{
    protected IndexSearchHasher $hasher;

    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(
            IndexSearchHasher::class,
            new IndexSearchHasher(new NoOpNormalizer(), new NoOpHasher(), $this->getIndexHashFixture()),
        );
    }

    public function testGetLastnameHash(): void
    {
        $hasher = new IndexSearchHasher(new NoOpNormalizer(), new NoOpHasher(), $this->getIndexHashFixture());

        $this->assertSame(
            '20000401#Jane',
            $hasher->getLastnameHash(),
            'The hash value is not composed the same as expected',
        );
    }

    public function testGetBsnHash(): void
    {
        $hasher = new IndexSearchHasher(new NoOpNormalizer(), new NoOpHasher(), $this->getIndexHashFixture());

        $this->assertSame(
            '123#20000401',
            $hasher->getBsnHash(),
            'The hash value is not composed the same as expected',
        );
    }

    public function testGetAddressHash(): void
    {
        $hasher = new IndexSearchHasher(new NoOpNormalizer(), new NoOpHasher(), $this->getIndexHashFixture());

        $this->assertSame(
            '0#20000401#9999XX#A',
            $hasher->getAddressHash(),
            'The hash value is not composed the same as expected',
        );
    }

    protected function getIndexHashFixture(): IndexHash
    {
        return new IndexHash(
            dateOfBirth: CarbonImmutable::createFromFormat('Y-m-d', '2000-04-01'),
            lastname: 'Jane',
            lastThreeBsnDigits: '123',
            postalCode: '9999XX',
            houseNumber: '0',
            houseNumberSuffix: 'A',
        );
    }
}
