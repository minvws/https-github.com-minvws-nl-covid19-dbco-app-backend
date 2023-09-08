<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\EloquentTask\General;

use App\Services\SearchHash\EloquentTask\General\GeneralHash;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
class GeneralHashTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(
            GeneralHash::class,
            new GeneralHash(
                dateOfBirth: $this->faker->dateTimeBetween(),
                lastname: $this->faker->lastName(),
                phone: $this->faker->phoneNumber(),
            ),
        );
    }
}
