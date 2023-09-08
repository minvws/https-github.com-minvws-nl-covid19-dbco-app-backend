<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar\StrategyLoader;

use App\Dto\Admin\CreateDateOperationDto;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Repositories\Policy\DateOperationRepository;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\DateOperationMutation;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use MinVWS\DBCO\Enum\Models\UnitOfTime;
use RuntimeException;
use Webmozart\Assert\Assert;

/**
 * @implements DateOperationStrategyLoader<CreateDateOperationDto>
 */
abstract class AbstractDateOperationStrategyLoader implements DateOperationStrategyLoader
{
    public function __construct(
        private readonly DateOperationRepository $dateOperationRepository,
        private readonly CalendarItemConfigStrategy $calendarItemConfigStrategy,
    )
    {
    }

    public function get(CalendarItemConfigStrategy $calendarItemConfigStrategy): Collection
    {
        return $this->dateOperationRepository->getDateOperations($calendarItemConfigStrategy);
    }

    public function deleteAll(CalendarItemConfigStrategy $calendarItemConfigStrategy): void
    {
        $this->dateOperationRepository->deleteAllDateOperations($calendarItemConfigStrategy);
    }

    public function set(CalendarItemConfigStrategy $calendarItemConfigStrategy, Collection $data): Collection
    {
        return $this->dateOperationRepository
            ->insertDateOperationDtosByCalendarItemConfig($calendarItemConfigStrategy, $data);
    }

    public function getInitializeData(CalendarItemConfigStrategy $calendarItemConfigStrategy): Collection
    {
        $this->calendarItemConfigStrategy->loadMissing('calendarItemConfig.calendarItem');

        return $this->getDateOperationsData(
            $calendarItemConfigStrategy->calendarItemConfig->calendarItem->calendar_item_enum,
            $calendarItemConfigStrategy->calendarItemConfig->calendarItem->person_type_enum,
        );
    }

    /**
     * @return Collection<CreateDateOperationDto>
     */
    abstract protected function getDateOperationsData(CalendarItemEnum $calendarItem, PolicyPersonType $personType): Collection;

    protected function createDateOperationDto(PolicyPersonType $personType, DateOperationIdentifier $identifier): CreateDateOperationDto
    {
        $originDate = match ($personType) {
            PolicyPersonType::index() => IndexOriginDate::defaultItem(),
            PolicyPersonType::contact() => ContactOriginDate::defaultItem(),
            default => throw new RuntimeException('Unknown person type given.'),
        };

        Assert::notNull($originDate);

        return new CreateDateOperationDto(
            identifier: $identifier,
            mutation: DateOperationMutation::add(),
            amount: 0,
            unitOfTime: UnitOfTime::day(),
            originDate: $originDate,
        );
    }
}
