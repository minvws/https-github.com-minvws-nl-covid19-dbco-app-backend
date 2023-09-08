<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarView;
use App\Models\Policy\PolicyVersion;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use MinVWS\DBCO\Enum\Models\CalendarView as CalendarViewEnum;
use Webmozart\Assert\Assert;

final class CalendarViewPopulator
{
    public function __construct(
        private readonly Connection $db,
    )
    {
    }

    public function populate(PolicyVersion $policyVersion): void
    {
        $this->db->transaction(function () use ($policyVersion): void {
            foreach ($this->getCalendarViewData($policyVersion) as $calendarViewData) {
                $calendarView = CalendarView::query()->create([
                    'policy_version_uuid' => $policyVersion->uuid,
                    'label' => $calendarViewData['label'],
                    'calendar_view_enum' => $calendarViewData['calendar_view_enum'],
                ]);
                $calendarView->calendarItems()->sync($calendarViewData['calendar_items']);
            }
        });
    }

    private function getCalendarViewData(PolicyVersion $policyVersion): array
    {
        return [
            [
                'label' => 'Index sidebar',
                'calendar_view_enum' => CalendarViewEnum::indexSidebar(),
                'calendar_items' => [
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::SourcePeriod)->uuid,
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::ContagiousPeriod)->uuid,
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::DateOfSymptomOnset)->uuid,
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::DateOfTestIndex)->uuid,
                ],
            ],
            [
                'label' => 'Contact besmettelijk sidebar',
                'calendar_view_enum' => CalendarViewEnum::indexTaskContagiousSidebar(),
                'calendar_items' => [],
            ],
            [
                'label' => 'Contact bron sidebar',
                'calendar_view_enum' => CalendarViewEnum::indexTaskSourceSidebar(),
                'calendar_items' => [
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::SourcePeriod)->uuid,
                ],
            ],
            [
                'label' => 'Contact bron datum selectie',
                'calendar_view_enum' => CalendarViewEnum::indexTaskSourceTable(),
                'calendar_items' => [
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::SourcePeriod)->uuid,
                ],
            ],
            [
                'label' => 'Contact besmettelijk datum selectie',
                'calendar_view_enum' => CalendarViewEnum::indexTaskContagiousTable(),
                'calendar_items' => [
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::ContagiousPeriod)->uuid,
                ],
            ],
            [
                'label' => 'Context sidebar',
                'calendar_view_enum' => CalendarViewEnum::indexContextSidebar(),
                'calendar_items' => [
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::SourcePeriod)->uuid,
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::ContagiousPeriod)->uuid,
                ],
            ],
            [
                'label' => 'Context datum selectie',
                'calendar_view_enum' => CalendarViewEnum::indexContextTable(),
                'calendar_items' => [
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::SourcePeriod)->uuid,
                    $this->getCalendarItem($policyVersion, PopulatorReferenceEnum::ContagiousPeriod)->uuid,
                ],
            ],
        ];
    }

    private function getBuilder(PolicyVersion $policyVersion): Builder
    {
        return CalendarItem::query()->where('policy_version_uuid', $policyVersion->uuid);
    }

    public function getCalendarItem(PolicyVersion $policyVersion, PopulatorReferenceEnum $reference): CalendarItem
    {
        $model = $this->getBuilder($policyVersion)->where('populator_reference_enum', $reference)->firstOrFail();
        Assert::isInstanceOf($model, CalendarItem::class);
        return $model;
    }
}
