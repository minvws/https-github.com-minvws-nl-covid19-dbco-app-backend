<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Dto\Admin\CreateCalendarItemDto;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyVersion;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use MinVWS\DBCO\Enum\Models\FixedCalendarItem;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

final class CalendarItemPopulator
{
    public function __construct(
        private readonly Connection $db,
        private readonly CalendarItemRepository $calendarItemRepository,
    )
    {
    }

    /**
     * @return Collection<CalendarItem>
     */
    public function populate(PolicyVersion $policyVersion): Collection
    {
        return $this->db->transaction(function () use ($policyVersion): Collection {
            return Collection::make([
                // index
                new CreateCalendarItemDto(
                    label: 'Bronperiode',
                    personType: PolicyPersonType::index(),
                    itemType: CalendarItemEnum::period(),
                    color: CalendarPeriodColor::lightBlue(),
                    fixedCalendarItemType: FixedCalendarItem::source(),
                    populatorReferenceEnum: PopulatorReferenceEnum::SourcePeriod,
                ),
                new CreateCalendarItemDto(
                    label: 'Besmettelijke periode',
                    personType: PolicyPersonType::index(),
                    itemType: CalendarItemEnum::period(),
                    color: CalendarPeriodColor::lightPink(),
                    fixedCalendarItemType: FixedCalendarItem::contagious(),
                    populatorReferenceEnum: PopulatorReferenceEnum::ContagiousPeriod,
                ),
                new CreateCalendarItemDto(
                    label: 'Eerste ziektedag',
                    personType: PolicyPersonType::index(),
                    itemType: CalendarItemEnum::point(),
                    color: CalendarPointColor::red(),
                    populatorReferenceEnum: PopulatorReferenceEnum::DateOfSymptomOnset,
                ),
                new CreateCalendarItemDto(
                    label: 'Testdatum',
                    personType: PolicyPersonType::index(),
                    itemType: CalendarItemEnum::point(),
                    color: CalendarPointColor::orange(),
                    populatorReferenceEnum: PopulatorReferenceEnum::DateOfTestIndex,
                ),

                // contact
                new CreateCalendarItemDto(
                    label: 'Quarantaine periode',
                    personType: PolicyPersonType::contact(),
                    itemType: CalendarItemEnum::period(),
                    color: CalendarPeriodColor::lightGreen(),
                    populatorReferenceEnum: PopulatorReferenceEnum::QuarantinePeriod,
                ),
                new CreateCalendarItemDto(
                    label: 'Einde quarantaine',
                    personType: PolicyPersonType::contact(),
                    itemType: CalendarItemEnum::point(),
                    color: CalendarPointColor::green(),
                    populatorReferenceEnum: PopulatorReferenceEnum::DateOfQuarantineEnd,
                ),
                new CreateCalendarItemDto(
                    label: 'Testen op dag',
                    personType: PolicyPersonType::contact(),
                    itemType: CalendarItemEnum::point(),
                    color: CalendarPointColor::orange(),
                    populatorReferenceEnum: PopulatorReferenceEnum::DateOfTestContact,
                ),
            ])
                ->map(
                    fn (CreateCalendarItemDto $dto): CalendarItem
                        => $this->calendarItemRepository->createCalendarItem($policyVersion->uuid, $dto)
                );
        });
    }
}
