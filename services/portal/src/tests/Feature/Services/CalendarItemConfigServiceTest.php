<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Services\CalendarItemConfigService;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarItemConfig')]
final class CalendarItemConfigServiceTest extends FeatureTestCase
{
    public function testCreateDefaultCalendarItemConfigsForNewPolicyGuideline(): void
    {
        Event::fake();

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuidelines = PolicyGuideline::factory()->recycle($policyVersion)->count(2)->create();
        $calendarItems = CalendarItem::factory()
            ->point()
            ->recycle($policyVersion)
            ->count(1)
            ->create()
            ->add(CalendarItem::factory()->period()->recycle($policyVersion)->create());
        $policyGuidelines
            ->crossJoin($calendarItems)
            ->map(static fn (array $models)
                => CalendarItemConfig::factory()->recycle($policyVersion, ...$models)->withStrategies()->create());

        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();

        /** @var CalendarItemConfigService $service */
        $service = $this->app->make(CalendarItemConfigService::class);
        $service->createDefaultCalendarItemConfigsForNewPolicyGuideline($policyGuideline);

        $this->assertDatabaseCount(PolicyVersion::class, 1);
        $this->assertDatabaseCount(PolicyGuideline::class, 3);
        $this->assertDatabaseCount(CalendarItem::class, 2);
        $this->assertDatabaseCount(CalendarItemConfig::class, 6);
        $this->assertDatabaseCount(CalendarItemConfigStrategy::class, 9);

        $this->assertEqualsCanonicalizing(
            $calendarItems->pluck('uuid'),
            $policyGuideline->calendarItemConfigs->pluck('calendar_item_uuid'),
        );
    }

    public function testCreateDefaultCalendarItemConfigsForNewCalendarItem(): void
    {
        Event::fake();

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuidelines = PolicyGuideline::factory()->recycle($policyVersion)->count(2)->create();
        $calendarItems = CalendarItem::factory()
            ->point()
            ->recycle($policyVersion)
            ->count(1)
            ->create()
            ->add(CalendarItem::factory()->period()->recycle($policyVersion)->create());

        $policyGuidelines
            ->crossJoin($calendarItems)
            ->map(static fn (array $models)
                => CalendarItemConfig::factory()->recycle($policyVersion, ...$models)->withStrategies()->create());

        $calendarItem = CalendarItem::factory()->point()->recycle($policyVersion)->create();

        /** @var CalendarItemConfigService $service */
        $service = $this->app->make(CalendarItemConfigService::class);
        $service->createDefaultCalendarItemConfigsForNewCalendarItem($calendarItem);

        $this->assertDatabaseCount(PolicyVersion::class, 1);
        $this->assertDatabaseCount(PolicyGuideline::class, 2);
        $this->assertDatabaseCount(CalendarItem::class, 3);
        $this->assertDatabaseCount(CalendarItemConfig::class, 6);
        $this->assertDatabaseCount(CalendarItemConfigStrategy::class, 8);

        $policyGuidelines->pluck('uuid')
            ->each(function (string $policyGuidelineUuid) use ($calendarItem): void {
                $this->assertDatabaseHas('calendar_item_config', [
                    'policy_guideline_uuid' => $policyGuidelineUuid,
                    'calendar_item_uuid' => $calendarItem->uuid,
                ]);
            });
    }
}
