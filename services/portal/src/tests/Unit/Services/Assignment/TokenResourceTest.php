<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\TokenResource;
use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
final class TokenResourceTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $tokenResource = new TokenResource(
            mod: AssignmentModelEnum::cases()[0],
            ids: [],
        );

        $this->assertInstanceOf(TokenResource::class, $tokenResource);
        $this->assertInstanceOf(Arrayable::class, $tokenResource);
    }

    public function testToArray(): void
    {
        $data = [
            'mod' => $this->faker->randomElement(AssignmentModelEnum::cases()),
            'ids' => $this->faker->words(),
        ];

        $tokenResource = new TokenResource(mod: $data['mod'], ids: $data['ids']);

        $expected = [
            'mod' => $data['mod']->value,
            'ids' => $data['ids'],
        ];

        $this->assertSame($expected, $tokenResource->toArray());
    }
}
