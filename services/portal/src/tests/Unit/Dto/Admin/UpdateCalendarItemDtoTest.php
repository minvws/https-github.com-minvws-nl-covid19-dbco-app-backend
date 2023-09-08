<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Admin;

use App\Dto\Admin\UpdateCalendarItemDto;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use PhpOption\None;
use PhpOption\Some;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('policy')]
#[Group('calendarItem')]
final class UpdateCalendarItemDtoTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $dto = new UpdateCalendarItemDto(
            label: None::create(),
            color: None::create(),
        );

        $this->assertInstanceOf(UpdateCalendarItemDto::class, $dto);
    }

    #[DataProvider('getToEloquentAttributes')]
    public function testToArray(array $data, array $expected): void
    {
        $this->assertEqualsCanonicalizing($expected, (new UpdateCalendarItemDto(...$data))->toEloquentAttributes());
    }

    public static function getToEloquentAttributes(): array
    {
        return [
            'returns an empty array if it does not have some values' => [
                'data' => [
                    'label' => None::create(),
                    'color' => None::create(),
                ],
                'expected' => [],
            ],
            'returns label only' => [
                'data' => [
                    'label' => Some::create('my label'),
                    'color' => None::create(),
                ],
                'expected' => [
                    'label' => 'my label',
                ],
            ],
            'returns all values' => [
                'data' => [
                    'label' => Some::create('my label'),
                    'color' => Some::create(CalendarPointColor::red()),
                ],
                'expected' => [
                    'label' => 'my label',
                    'color' => CalendarPointColor::red(),
                ],
            ],
        ];
    }
}
