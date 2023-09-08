<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\CalendarItemCreated;
use App\Events\PolicyGuidelineCreated;
use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('calendarItem')]
final class ApiCalendarItemConfigControllerTest extends FeatureTestCase
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

    // LIST
    public function testListCalendarItemConfigRequiresAuthentication(): void
    {
        $this
            ->getJson(
                sprintf('/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config', $this->faker->uuid, $this->faker->uuid),
            )
            ->assertUnauthorized();
    }

    public function testListCalendarItemConfigRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();

        $this
            ->getJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                ),
            )
            ->assertForbidden();
    }

    public function testListCalendarItemConfig(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create([
            'person_type' => PolicyPersonType::index(),
        ]);
        $calendarItems = CalendarItem::factory()->count(5)->create([
            'person_type_enum' => PolicyPersonType::index(),
        ]);

        CalendarItemConfig::factory()->count(5)->recycle($policyGuideline, $calendarItems)->withStrategies()->create();

        $this
            ->getJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                ),
            )
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure(['*' => self::RESPONSE_STRUCTURE])
            ->assertJsonCount(5);
    }

    // UPDATE
    public function testUpdateCalendarItemConfigRequiresAuthentication(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline)->create();

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                ),
                [
                    'isHidden' => $this->faker->boolean(),
                ],
            )
            ->assertUnauthorized();
    }

    public function testUpdateCalendarItemConfigRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline)->create();

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                ),
                [
                    'isHidden' => $this->faker->boolean(),
                ],
            )
            ->assertForbidden();
    }

    public function testUpdateCalendarItemConfig(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create([
            'fixed_calendar_item_enum' => null,
        ]);
        $calendarItemConfig = CalendarItemConfig::factory()->recycle($policyGuideline, $calendarItem)->create();

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s',
                    $policyVersion->uuid,
                    $policyGuideline->uuid,
                    $calendarItemConfig->uuid,
                ),
                [
                    'isHidden' => !$calendarItemConfig->is_hidden,
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas('calendar_item_config', [
            'uuid' => $calendarItemConfig->uuid,
            'is_hidden' => !$calendarItemConfig->is_hidden,
        ]);
    }

    public function testUpdateCalendarItemConfigNotFound(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $this
            ->putJson(
                sprintf(
                    '/api/admin/policy-version/%s/policy-guideline/%s/calendar-item-config/%s',
                    $this->faker->uuid(),
                    $this->faker->uuid(),
                    $this->faker->uuid(),
                ),
                [
                    'isHidden' => $this->faker->boolean(),
                ],
            )
            ->assertNotFound();
    }
}
