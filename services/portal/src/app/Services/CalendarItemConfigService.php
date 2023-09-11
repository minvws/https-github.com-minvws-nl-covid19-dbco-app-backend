<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyGuideline;
use App\Repositories\Policy\CalendarItemConfigRepository;
use App\Repositories\Policy\CalendarItemConfigStrategyRepository;
use App\Repositories\Policy\CalendarItemRepository;
use App\Repositories\Policy\PolicyGuidelineRepository;
use Illuminate\Database\Connection;

final class CalendarItemConfigService
{
    public function __construct(
        private readonly Connection $db,
        private readonly CalendarItemConfigRepository $calendarItemConfigRepository,
        private readonly CalendarItemRepository $calendarItemRepository,
        private readonly CalendarItemConfigStrategyRepository $calendarItemConfigStrategyRepository,
        private readonly PolicyGuidelineRepository $policyGuidelineRepository,
    )
    {
    }

    public function createDefaultCalendarItemConfigsForNewPolicyGuideline(PolicyGuideline $policyGuideline): void
    {
        $this->db->transaction(function () use ($policyGuideline): void {
            $calendarItemConfigs = $this->calendarItemConfigRepository->createDefaultCalendarItemConfigsForNewPolicyGuideline(
                $policyGuideline->uuid,
                $this->calendarItemRepository->getCalendarItems($policyGuideline->policy_version_uuid),
            );

            $this->calendarItemConfigStrategyRepository
                ->createDefaultCalendarItemConfigStrategiesForNewCalendarItemConfigs($calendarItemConfigs);
        });
    }

    public function createDefaultCalendarItemConfigsForNewCalendarItem(CalendarItem $calendarItem,): void
    {
        $this->db->transaction(function () use ($calendarItem): void {
            $this->calendarItemRepository->loadMissing($calendarItem, 'policyVersion');

            $calendarItemConfigs = $this->calendarItemConfigRepository->createDefaultCalendarItemConfigsForNewCalendarItem(
                $calendarItem,
                $this->policyGuidelineRepository->getPolicyGuidelinesByPolicyVersion($calendarItem->policyVersion),
            );

            $this->calendarItemConfigStrategyRepository
                ->createDefaultCalendarItemConfigStrategiesForNewCalendarItemConfigs($calendarItemConfigs);
        });
    }
}
