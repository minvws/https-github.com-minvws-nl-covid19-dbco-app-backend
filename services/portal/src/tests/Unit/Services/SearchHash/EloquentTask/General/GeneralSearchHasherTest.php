<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\EloquentTask\General;

use App\Services\SearchHash\EloquentTask\General\GeneralHash;
use App\Services\SearchHash\EloquentTask\General\GeneralSearchHasher;
use App\Services\SearchHash\Hasher\NoOpHasher;
use App\Services\SearchHash\Normalizer\NoOpNormalizer;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
class GeneralSearchHasherTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(
            GeneralSearchHasher::class,
            new GeneralSearchHasher(new NoOpNormalizer(), new NoOpHasher(), new stdClass()),
        );
    }

    public function testGetPhoneHash(): void
    {
        $hasher = new GeneralSearchHasher(new NoOpNormalizer(), new NoOpHasher(), $this->getContactHashFixture());

        $this->assertSame(
            '06 12345678#20100101',
            $hasher->getPhoneHash(),
            'The hash value is not composed the same as expected',
        );
    }

    protected function getContactHashFixture(): GeneralHash
    {
        return new GeneralHash(
            dateOfBirth: CarbonImmutable::createFromFormat('Y-m-d', '2010-01-01'),
            lastname: null,
            phone: '+31612345678',
        );
    }
}
