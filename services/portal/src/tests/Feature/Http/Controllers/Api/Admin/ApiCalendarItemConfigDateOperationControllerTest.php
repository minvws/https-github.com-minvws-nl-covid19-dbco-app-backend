<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\CalendarItemCreated;
use App\Events\PolicyGuidelineCreated;
use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnums;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationMutation;
use MinVWS\DBCO\Enum\Models\DateOperationRelativeDay;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use MinVWS\DBCO\Enum\Models\UnitOfTime;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_merge;
use function sprintf;

#[Group('policy')]
#[Group('calendarItem')]
final class ApiCalendarItemConfigDateOperationControllerTest extends FeatureTestCase
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
    public function testUpdateCalendarItemConfigDateOperationRequiresAuthentication(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline)->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->recycle($calendarItemConfig)->create();
        $dateOperation = DateOperation::factory()->recycle($calendarItemConfigStrategy)->create();

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s/calendar-item-config-strategy/%s/date-operation/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                    $calendarItemConfigStrategy->uuid,
                    $dateOperation->uuid,
                ),
                [
                    'relativeDay' => DateOperationRelativeDay::zero(),
                    'originDateType' => IndexOriginDate::dateOfTest(),
                ],
            )
            ->assertUnauthorized();
    }

    public function testUpdateCalendarItemConfigDateOperationRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline)->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->recycle($calendarItemConfig)->create();
        $dateOperation = DateOperation::factory()->recycle($calendarItemConfigStrategy)->create();

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s/calendar-item-config-strategy/%s/date-operation/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                    $calendarItemConfigStrategy->uuid,
                    $dateOperation->uuid,
                ),
                [
                    'relativeDay' => DateOperationRelativeDay::zero(),
                    'originDateType' => IndexOriginDate::dateOfTest(),
                ],
            )
            ->assertForbidden();
    }

    public function testUpdateCalendarItemConfigDateOperation(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create([
            'person_type_enum' => PolicyPersonType::index(),
            'calendar_item_enum' => CalendarItemEnums::point(),
        ]);
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline, $calendarItem)->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->recycle($calendarItemConfig)->create([
            'strategy_type' => PointCalendarStrategyType::fixedStrategy(),
        ]);
        $dateOperation = DateOperation::factory()->recycle($calendarItemConfigStrategy)->create([
            'amount' => 3,
            'mutation_type' => DateOperationMutation::add(),
            'unit_of_time_type' => UnitOfTime::day(),
            'origin_date_type' => IndexOriginDate::dateOfTest(),
        ]);

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s/calendar-item-config-strategy/%s/date-operation/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                    $calendarItemConfigStrategy->uuid,
                    $dateOperation->uuid,
                ),
                [
                    'relativeDay' => DateOperationRelativeDay::zero(),
                    'originDateType' => IndexOriginDate::symptomsOnset(),
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas('date_operation', [
            'uuid' => $dateOperation->uuid,
            'amount' => 0,
            'mutation_type' => DateOperationMutation::add(),
            'unit_of_time_type' => UnitOfTime::day(),
            'origin_date_type' => IndexOriginDate::symptomsOnset(),
        ]);
    }

    public function testUpdateCalendarItemConfigDateOperationNotFound(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s/calendar-item-config-strategy/%s/date-operation/%s',
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                ),
                [
                    'relativeDay' => $this->faker->randomElement(DateOperationRelativeDay::all()),
                    'originDateType' => $this->faker->randomElement(array_merge(IndexOriginDate::all(), ContactOriginDate::all())),
                ],
            )
            ->assertNotFound();
    }
}
