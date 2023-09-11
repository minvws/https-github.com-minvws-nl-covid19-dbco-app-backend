<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\EloquentCase\Contact;

use App\Services\SearchHash\EloquentCase\Contact\ContactHash;
use App\Services\SearchHash\EloquentCase\Contact\ContactSearchHasher;
use App\Services\SearchHash\Hasher\NoOpHasher;
use App\Services\SearchHash\Normalizer\NoOpNormalizer;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class ContactSearchHasherTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(
            ContactSearchHasher::class,
            new ContactSearchHasher(new NoOpNormalizer(), new NoOpHasher(), new stdClass()),
        );
    }

    public function testGetPhoneHash(): void
    {
        $hasher = new ContactSearchHasher(new NoOpNormalizer(), new NoOpHasher(), $this->getContactHashFixture());

        $this->assertSame(
            '06 12345678#20100101',
            $hasher->getPhoneHash(),
            'The hash value is not composed the same as expected',
        );
    }

    protected function getContactHashFixture(): ContactHash
    {
        return new ContactHash(
            dateOfBirth: CarbonImmutable::createFromFormat('Y-m-d', '2010-01-01'),
            phone: '+31612345678',
        );
    }
}
