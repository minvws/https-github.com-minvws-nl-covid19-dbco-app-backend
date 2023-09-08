<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Admin;

use App\Dto\Admin\UpdateDateOperationDto;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationRelativeDay;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function array_merge;

#[Group('policy')]
#[Group('calendarItem')]
final class UpdateDateOperationDtoTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $dto = new UpdateDateOperationDto(
            relativeDay: DateOperationRelativeDay::zero(),
            originDateType: IndexOriginDate::dateOfTest(),
        );

        $this->assertInstanceOf(UpdateDateOperationDto::class, $dto);
    }

    public function testToEloquentAttributes(): void
    {
        $dto = new UpdateDateOperationDto(
            relativeDay: $relativeDay = $this->faker->randomElement(DateOperationRelativeDay::all()),
            originDateType: $originDateType = $this->faker->randomElement(array_merge(IndexOriginDate::all(), ContactOriginDate::all())),
        );

        $this->assertEqualsCanonicalizing([
            'relative_day' => $relativeDay,
            'origin_date_type' => $originDateType,
        ], $dto->toEloquentAttributes());
    }
}
