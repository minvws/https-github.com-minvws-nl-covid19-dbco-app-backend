<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Dto\Admin\CreateCalendarItemDto;
use App\Dto\Admin\UpdateCalendarItemDto;
use App\Models\Policy\CalendarItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LogicException;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use RuntimeException;

use function sprintf;

final class CalendarItemRepository
{
    /**
     * @return Collection<int,CalendarItem>
     */
    public function getCalendarItems(string $policyVersionUuid, ?PolicyPersonType $policyPersonType = null, ?CalendarItemEnum $calendarItemEnum = null): Collection
    {
        /** @var Collection<int,CalendarItem> */
        return CalendarItem::query()
            ->when(
                $policyPersonType,
                static fn (Builder $query, ?PolicyPersonType $policyPersonType) => $query->where('person_type_enum', $policyPersonType),
            )
            ->when(
                $calendarItemEnum,
                static fn (Builder $query, ?CalendarItemEnum $calendarItemEnum) => $query->where('calendar_item_enum', $calendarItemEnum),
            )
            ->with('policyVersion')
            ->where('policy_version_uuid', $policyVersionUuid)
            ->orderByRaw('`fixed_calendar_item_enum` IS NULL')
            ->orderBy('created_at')
            ->orderBy('label')
            ->get();
    }

    public function deleteCalendarItem(CalendarItem $calendarItem): bool
    {
        try {
            return $calendarItem->delete() ?? false;
        } catch (LogicException) {
            return false;
        }
    }

    public function createCalendarItem(string $policyVersionUuid, CreateCalendarItemDto $dto): CalendarItem
    {
        return CalendarItem::query()
            ->create($dto->toEloquentAttributes() + ['policy_version_uuid' => $policyVersionUuid]);
    }

    public function updateCalendarItem(CalendarItem $calendarItem, UpdateCalendarItemDto $dto): CalendarItem
    {
        if (!$calendarItem->update($dto->toEloquentAttributes())) {
            throw new RuntimeException(sprintf('Failed to update Calendar item with UUID: "%s"', $calendarItem->uuid));
        }

        return $calendarItem;
    }

    public function loadMissing(CalendarItem $calendarItem, string ...$relations): CalendarItem
    {
        return $calendarItem->loadMissing(...$relations);
    }
}
