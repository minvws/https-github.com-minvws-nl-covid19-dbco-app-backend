<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Admin;

use App\Dto\Admin\UpdateCalendarItemConfigDto;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('policy')]
#[Group('calendarItem')]
final class UpdateCalendarItemConfigDtoTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $dto = new UpdateCalendarItemConfigDto(isHidden: false);

        $this->assertInstanceOf(UpdateCalendarItemConfigDto::class, $dto);
    }

    public function testToEloquentAttributes(): void
    {
        $dto = new UpdateCalendarItemConfigDto(isHidden: $isHidden = $this->faker->boolean);

        $this->assertEqualsCanonicalizing([
            'is_hidden' => $isHidden,
        ], $dto->toEloquentAttributes());
    }
}
