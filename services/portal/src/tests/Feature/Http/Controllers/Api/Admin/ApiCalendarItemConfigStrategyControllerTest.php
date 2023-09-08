<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\CalendarItemCreated;
use App\Events\PolicyGuidelineCreated;
use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnums;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_merge;
use function sprintf;

#[Group('policy')]
#[Group('calendarItem')]
final class ApiCalendarItemConfigStrategyControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class, PolicyGuidelineCreated::class, CalendarItemCreated::class]);
    }

    public const RESPONSE_STRUCTURE = [
        'uuid',
        'label',
        'isHidden',
        'isHideable',
        'itemType',
        'strategies',
    ];

    // UPDATE
    public function testUpdateCalendarItemConfigStrategyRequiresAuthentication(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline)->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->recycle($calendarItemConfig)->create();

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s/calendar-item-config-strategy/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                    $calendarItemConfigStrategy->uuid,
                ),
                [
                    'strategyType' => $this->faker->randomElement(
                        array_merge(PointCalendarStrategyType::allValues(), PeriodCalendarStrategyType::allValues()),
                    ),
                ],
            )
            ->assertUnauthorized();
    }

    public function testUpdateCalendarItemConfigStrategyRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline)->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->recycle($calendarItemConfig)->create();

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s/calendar-item-config-strategy/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                    $calendarItemConfigStrategy->uuid,
                ),
                [
                    'strategyType' => $this->faker->randomElement(
                        array_merge(PointCalendarStrategyType::allValues(), PeriodCalendarStrategyType::allValues()),
                    ),
                ],
            )
            ->assertForbidden();
    }

    public function testUpdateCalendarItemConfigStrategy(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create([
            'calendar_item_enum' => CalendarItemEnums::point(),
        ]);
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline, $calendarItem)->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->recycle($calendarItemConfig)->create([
            'strategy_type' => PointCalendarStrategyType::fixedStrategy(),
        ]);

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s/calendar-item-config-strategy/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                    $calendarItemConfigStrategy->uuid,
                ),
                [
                    'strategyType' => PointCalendarStrategyType::flexStrategy(),
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas('calendar_item_config_strategy', [
            'uuid' => $calendarItemConfigStrategy->uuid,
            'strategy_type' => PointCalendarStrategyType::flexStrategy(),
        ]);
    }

    public function testUpdateCalendarItemConfigStrategyNotFound(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s/calendar-item-config-strategy/%s',
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                ),
                [
                    'strategyType' => $this->faker->randomElement(
                        array_merge(PointCalendarStrategyType::allValues(), PeriodCalendarStrategyType::allValues()),
                    ),
                ],
            )
            ->assertNotFound();
    }
}
