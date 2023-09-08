<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Admin\CreateCalendarItemDto;
use App\Dto\Admin\UpdateCalendarItemDto;
use App\Models\Policy\CalendarItem;
use App\Repositories\Policy\CalendarItemRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

final class CalendarItemService
{
    public function __construct(private CalendarItemRepository $calendarItemRepository)
    {
    }

    /**
     * @return Collection<array-key,CalendarItem>
     */
    public function getCalendarItems(string $policyVersionUuid, ?PolicyPersonType $policyPersonType = null): Collection
    {
        return $this->calendarItemRepository->getCalendarItems($policyVersionUuid, $policyPersonType);
    }

    public function deleteCalendarItem(CalendarItem $calendarItem): bool
    {
        if (!$calendarItem->isDeletable()) {
            throw ValidationException::withMessages([
                'general' => 'This calendar item cannot be deleted!',
            ]);
        }

        return $this->calendarItemRepository->deleteCalendarItem($calendarItem);
    }

    public function createCalendarItem(string $policyVersionUuid, CreateCalendarItemDto $dto): CalendarItem
    {
        return $this->calendarItemRepository->createCalendarItem($policyVersionUuid, $dto);
    }

    public function updateCalendarItem(CalendarItem $calendarItem, UpdateCalendarItemDto $dto): CalendarItem
    {
        return $this->calendarItemRepository->updateCalendarItem($calendarItem, $dto);
    }
}
