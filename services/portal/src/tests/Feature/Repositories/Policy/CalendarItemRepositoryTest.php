<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Dto\Admin\CreateCalendarItemDto;
use App\Dto\Admin\UpdateCalendarItemDto;
use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\CalendarItemRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use MinVWS\DBCO\Enum\Models\FixedCalendarItem;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use Mockery;
use Mockery\MockInterface;
use PhpOption\None;
use PhpOption\Some;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('calendarItem')]
final class CalendarItemRepositoryTest extends FeatureTestCase
{
    private CalendarItemRepository $calendarItemRepository;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->calendarItemRepository = $this->app->make(CalendarItemRepository::class);
    }

    public function testGetCalendarItems(): void
    {
        $now = CarbonImmutable::now();

        $policyVersionOne = PolicyVersion::factory()->create();
        $policyVersionTwo = PolicyVersion::factory()->create();

        $expectedCalendarItems = Collection::make([
            [
                'label' => 'A',
                'person_type_enum' => PolicyPersonType::index(),
                'fixed_calendar_item_enum' => FixedCalendarItem::contagious(),
                'created_at' => $now->subDays(3),
            ],
            [
                'label' => 'B',
                'person_type_enum' => PolicyPersonType::index(),
                'fixed_calendar_item_enum' => FixedCalendarItem::source(),
                'created_at' => $now->subDays(2),
            ],
            [
                'label' => 'C',
                'person_type_enum' => PolicyPersonType::index(),
                'fixed_calendar_item_enum' => FixedCalendarItem::source(),
                'created_at' => $now->subDays(2),
            ],
            [
                'label' => 'D',
                'person_type_enum' => PolicyPersonType::index(),
                'fixed_calendar_item_enum' => null,
                'created_at' => $now->subDays(4),
            ],
            [
                'label' => 'E',
                'person_type_enum' => PolicyPersonType::index(),
                'fixed_calendar_item_enum' => null,
                'created_at' => $now->subDays(3),
            ],
            [
                'label' => 'F',
                'person_type_enum' => PolicyPersonType::index(),
                'fixed_calendar_item_enum' => null,
                'created_at' => $now->subDays(1),
            ],
        ]);

        $expectedCalendarItems
            ->shuffle()
            ->map(static fn (array $attributes): string => CalendarItem::factory()
                ->recycle($policyVersionOne)
                ->create($attributes)
                ->uuid);

        CalendarItem::factory()
            ->recycle($policyVersionOne)
            ->count(2)
            ->create([
                'person_type_enum' => PolicyPersonType::contact(),
            ]);

        CalendarItem::factory()
            ->recycle($policyVersionTwo)
            ->count(2)
            ->create([
                'person_type_enum' => PolicyPersonType::index(),
            ]);

        $actualCalendarItems = $this->calendarItemRepository->getCalendarItems($policyVersionOne->uuid, PolicyPersonType::index());

        $this->assertCount(6, $actualCalendarItems);
        $this->assertEquals(
            $expectedCalendarItems->map->label,
            $actualCalendarItems->map->label,
            'Calendar items are not sorted correctly',
        );
    }

    public function testDeleteCalendarItem(): void
    {
        $calendarItem1 = CalendarItem::factory()->create();
        $calendarItem2 = CalendarItem::factory()->create();

        $this->calendarItemRepository->deleteCalendarItem($calendarItem1);

        $this->assertDatabaseMissing(CalendarItem::class, ['uuid' => $calendarItem1->uuid]);
        $this->assertDatabaseHas(CalendarItem::class, ['uuid' => $calendarItem2->uuid]);
    }

    public function testDeleteCalendarItemReturnsFalseOnLogicException(): void
    {
        $calendarItemWithoutPrimaryKeyDefined = (new CalendarItem())->setKeyName(null);

        $this->assertFalse($this->calendarItemRepository->deleteCalendarItem($calendarItemWithoutPrimaryKeyDefined));
    }

    public function testDeleteCalendarItemReturnsFalseWhenModelDoesNotExist(): void
    {
        $calendarItem = new CalendarItem();

        $this->assertFalse($this->calendarItemRepository->deleteCalendarItem($calendarItem));
    }

    public function testCreateCalendarItem(): void
    {
        $data = [
            'label' => $this->faker->words(asText: true),
            'personType' => $this->faker->randomElement(PolicyPersonType::all()),
            'itemType' => CalendarItemEnum::point(),
            'color' => $this->faker->randomElement(CalendarPointColor::all()),
        ];
        $policyVersion = PolicyVersion::factory()->create();
        $dto = new CreateCalendarItemDto(...$data);

        $result = $this->calendarItemRepository->createCalendarItem($policyVersion->uuid, $dto);

        $this->assertDatabaseHas(CalendarItem::class, ['uuid' => $result->uuid]);
    }

    #[DataProvider('getUpdateCalendarItemData')]
    public function testUpdateCalendarItem(array $dto, array $expected): void
    {
        $initialCalendarItemData = [
            'label' => $this->faker->words(asText: true),
            'color_enum' => CalendarPointColor::red(),
        ];

        $policyVersion = PolicyVersion::factory()->create();
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create($initialCalendarItemData);

        $dto = new UpdateCalendarItemDto(label: $dto['label'], color: $dto['color']);

        $updatedCalendarItem = $this->calendarItemRepository->updateCalendarItem($calendarItem, $dto);

        $this->assertSame($calendarItem, $updatedCalendarItem);
        $this->assertEquals(
            $expected + $initialCalendarItemData,
            [
                'label' => $updatedCalendarItem->label,
                'color_enum' => $updatedCalendarItem->color_enum,
            ],
        );
    }

    public function testUpdateCalendarItemThrowingExceptionOnFailedUpdate(): void
    {
        /** @var CalendarItem&MockInterface $calendarItem */
        $calendarItem = Mockery::mock(CalendarItem::class);
        $calendarItem->shouldReceive('getAttribute')->with('uuid')->andReturn($this->faker->uuid);
        $calendarItem->shouldReceive('update')->once()->andReturnFalse();

        /** @var UpdateCalendarItemDto&MockInterface $dto */
        $dto = Mockery::mock(UpdateCalendarItemDto::class);
        $dto->shouldReceive('toEloquentAttributes')->once()->andReturn([]);

        $this->expectExceptionObject(new RuntimeException(sprintf('Failed to update Calendar item with UUID: "%s"', $calendarItem->uuid)));

        $this->calendarItemRepository->updateCalendarItem($calendarItem, $dto);
    }

    public function testLoadMissing(): void
    {
        /** @var CalendarItemRepository $calendarItemRepository */
        $calendarItemRepository = $this->app->make(CalendarItemRepository::class);

        $calendarItem = CalendarItem::factory()->make();
        $relations = ['foobar'];

        /** @var CalendarItem&MockInterface $CalendarItemMock */
        $CalendarItemMock = Mockery::mock(CalendarItem::class);
        $CalendarItemMock
            ->shouldReceive('loadMissing')
            ->with(...$relations)
            ->once()
            ->andReturn($calendarItem);

        $calendarItemRepository->loadMissing($CalendarItemMock, ...$relations);
    }

    public static function getUpdateCalendarItemData(): array
    {
        return [
            'updating all' => [
                'dto' => [
                    'label' => new Some('MY UPDATED LABEL'),
                    'color' => new Some(CalendarPointColor::green()),
                ],
                'expected' => [
                    'label' => 'MY UPDATED LABEL',
                    'color_enum' => 'green',
                ],
            ],
            'updating label only' => [
                'dto' => [
                    'label' => new Some('MY UPDATED LABEL'),
                    'color' => None::create(),
                ],
                'expected' => [
                    'label' => 'MY UPDATED LABEL',
                ],
            ],
            'updating status only' => [
                'dto' => [
                    'label' => None::create(),
                    'color' => new Some(CalendarPointColor::green()),
                ],
                'expected' => [
                    'color_enum' => CalendarPointColor::green(),
                ],
            ],
            'updating nothing' => [
                'dto' => [
                    'label' => None::create(),
                    'color' => None::create(),
                ],
                'expected' => [],
            ],
        ];
    }
}
