<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Dto\Admin\UpdateCalendarItemConfigStrategyDto;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarItemConfigStrategyIdentifierType;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use RuntimeException;

use function sprintf;

final class CalendarItemConfigStrategyRepository
{
    /**
     * @param EloquentCollection<CalendarItemConfig> $calendarItemConfigs
     *
     * @return EloquentCollection<CalendarItemConfigStrategy>
     */
    public function createDefaultCalendarItemConfigStrategiesForNewCalendarItemConfigs(EloquentCollection $calendarItemConfigs): EloquentCollection
    {
        /** @var EloquentCollection<array-key,CalendarItemConfig> $calendarItemConfigs */
        $calendarItemConfigs->loadMissing('calendarItem');

        /** @var EloquentCollection<CalendarItemConfigStrategy> */
        return $calendarItemConfigs
            ->flatMap(
                fn (CalendarItemConfig $calendarItemConfig): EloquentCollection
                    => $this->getNewDefaultCalendaritemConfigStrategies($calendarItemConfig->calendarItem, $calendarItemConfig),
            )
            ->pipeInto(EloquentCollection::class);
    }

    public function loadMissing(CalendarItemConfigStrategy $calendarItemConfigStrategy, string ...$relations): CalendarItemConfigStrategy
    {
        return $calendarItemConfigStrategy->loadMissing(...$relations);
    }

    private function getNewDefaultCalendaritemConfigStrategies(
        CalendarItem $calendarItem,
        CalendarItemConfig $calendarItemConfig,
    ): EloquentCollection
    {
        return $calendarItem->calendar_item_enum === CalendarItemEnum::point()
            ? $this->getPointDefaultCalendarItemConfigStrategy($calendarItemConfig)
            : $this->getPeriodDefaultCalendarItemConfigStrategy($calendarItemConfig);
    }

    /**
     * @return EloquentCollection<CalendarItemConfigStrategy>
     */
    private function getPointDefaultCalendarItemConfigStrategy(CalendarItemConfig $calendarItemConfig): EloquentCollection
    {
        return EloquentCollection::make([
            CalendarItemConfigStrategy::query()->create([
                'calendar_item_config_uuid' => $calendarItemConfig->uuid,
                'identifier_type' => CalendarItemConfigStrategyIdentifierType::point(),
                'strategy_type' => PointCalendarStrategyType::defaultItem(),
            ]),
        ]);
    }

    /**
     * @return EloquentCollection<CalendarItemConfigStrategy>
     */
    private function getPeriodDefaultCalendarItemConfigStrategy(CalendarItemConfig $calendarItemConfig): EloquentCollection
    {
        return EloquentCollection::make([
            CalendarItemConfigStrategy::query()->create([
                'calendar_item_config_uuid' => $calendarItemConfig->uuid,
                'identifier_type' => CalendarItemConfigStrategyIdentifierType::periodStart(),
                'strategy_type' => PeriodCalendarStrategyType::defaultItem(),
            ]),
            CalendarItemConfigStrategy::query()->create([
                'calendar_item_config_uuid' => $calendarItemConfig->uuid,
                'identifier_type' => CalendarItemConfigStrategyIdentifierType::periodEnd(),
                'strategy_type' => PeriodCalendarStrategyType::defaultItem(),
            ]),
        ]);
    }

    public function updateCalendarItemConfigStrategy(CalendarItemConfigStrategy $calendarItemConfigStrategy, UpdateCalendarItemConfigStrategyDto $dto): CalendarItemConfigStrategy
    {
        if (!$calendarItemConfigStrategy->update($dto->toEloquentAttributes())) {
            throw new RuntimeException(
                sprintf('Failed to update Calendar item config strategy with UUID: "%s"', $calendarItemConfigStrategy->uuid),
            );
        }

        return $calendarItemConfigStrategy;
    }
}
