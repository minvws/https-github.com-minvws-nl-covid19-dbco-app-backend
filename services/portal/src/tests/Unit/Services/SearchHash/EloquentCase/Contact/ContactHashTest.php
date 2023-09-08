<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\EloquentCase\Contact;

use App\Services\SearchHash\EloquentCase\Contact\ContactHash;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class ContactHashTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(
            ContactHash::class,
            new ContactHash(
                dateOfBirth: $this->faker->dateTimeBetween(),
                phone: $this->faker->phoneNumber(),
            ),
        );
    }
}
