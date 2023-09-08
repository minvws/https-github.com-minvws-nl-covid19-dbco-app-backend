<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Dto\Admin\UpdateCalendarItemConfigDto;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\PolicyGuideline;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use RuntimeException;

use function is_null;
use function sprintf;

final class CalendarItemConfigRepository
{
    /**
     * @param Collection<array-key,PolicyGuideline> $policyGuidelines
     *
     * @return EloquentCollection<CalendarItemConfig>
     */
    public function createDefaultCalendarItemConfigsForNewCalendarItem(CalendarItem $calendarItem, Collection $policyGuidelines): EloquentCollection
    {
        /** @var EloquentCollection<CalendarItemConfig> */
        return $policyGuidelines
            ->map(fn (PolicyGuideline $policyGuidelines): CalendarItemConfig
                => $this->newDefaultCalendarItemConfig($calendarItem, $policyGuidelines->uuid))
            ->pipeInto(EloquentCollection::class);
    }

    /**
     * @param Collection<array-key,CalendarItem> $calendarItems
     *
     * @return EloquentCollection<CalendarItemConfig>
     */
    public function createDefaultCalendarItemConfigsForNewPolicyGuideline(string $policyGuidelineUuid, Collection $calendarItems): EloquentCollection
    {
        /** @var EloquentCollection<CalendarItemConfig> */
        return $calendarItems
            ->map(fn (CalendarItem $calendarItem): CalendarItemConfig
                => $this->newDefaultCalendarItemConfig($calendarItem, $policyGuidelineUuid))
            ->pipeInto(EloquentCollection::class);
    }

    private function newDefaultCalendarItemConfig(CalendarItem $calendarItem, string $policyGuidelineUuid): CalendarItemConfig
    {
        return CalendarItemConfig::query()->create([
            'policy_guideline_uuid' => $policyGuidelineUuid,
            'calendar_item_uuid' => $calendarItem->uuid,
            'is_hidden' => true,
        ]);
    }

    /**
     * @return Collection<int, CalendarItemConfig>
     */
    public function getCalendarItemConfigsByPolicyGuideline(PolicyGuideline $policyGuideline): Collection
    {
        /** @var Collection<int,CalendarItemConfig> */
        return CalendarItemConfig::query()
            ->with('calendarItem')
            ->whereRelation('calendarItem', 'person_type_enum', $policyGuideline->person_type)
            ->where('policy_guideline_uuid', $policyGuideline->uuid)
            ->get()
            ->sortBy([
                static fn (CalendarItemConfig $a, CalendarItemConfig $b): int => $a->calendarItem->calendar_item_enum->value <=> $b->calendarItem->calendar_item_enum->value,
                static function (CalendarItemConfig $a, CalendarItemConfig $b): int {
                    $enumA = $a->calendarItem->fixed_calendar_item_enum;
                    $aHasValue = !is_null($enumA);

                    $enumB = $b->calendarItem->fixed_calendar_item_enum;
                    $bHasValue = !is_null($enumB);

                    if ($aHasValue && $bHasValue) {
                        return $enumA->value <=> $enumB->value;
                    }

                    return $bHasValue <=> $aHasValue;
                },
                static fn (CalendarItemConfig $a, CalendarItemConfig $b): int => $a->calendarItem->label <=> $b->calendarItem->label,
            ])
            ->values();
    }

    public function updateCalendarItemConfig(CalendarItemConfig $calendarItemConfig, UpdateCalendarItemConfigDto $dto): CalendarItemConfig
    {
        if (!$calendarItemConfig->update($dto->toEloquentAttributes())) {
            throw new RuntimeException(sprintf('Failed to update Calendar item config with UUID: "%s"', $calendarItemConfig->uuid));
        }

        return $calendarItemConfig;
    }
}
