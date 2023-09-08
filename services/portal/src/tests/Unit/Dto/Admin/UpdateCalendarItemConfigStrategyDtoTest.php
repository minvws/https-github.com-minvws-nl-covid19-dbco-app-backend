<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Admin;

use App\Dto\Admin\UpdateCalendarItemConfigStrategyDto;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function array_merge;

#[Group('policy')]
#[Group('calendarItem')]
final class UpdateCalendarItemConfigStrategyDtoTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $dto = new UpdateCalendarItemConfigStrategyDto(
            strategyType: PointCalendarStrategyType::fixedStrategy(),
        );

        $this->assertInstanceOf(UpdateCalendarItemConfigStrategyDto::class, $dto);
    }

    public function testToEloquentAttributes(): void
    {
        $dto = new UpdateCalendarItemConfigStrategyDto(
            strategyType: $strategyType = $this->faker->randomElement(
                array_merge(PointCalendarStrategyType::all(), PeriodCalendarStrategyType::all()),
            ),
        );

        $this->assertEqualsCanonicalizing([
            'strategy_type' => $strategyType,
        ], $dto->toEloquentAttributes());
    }
}
