<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\CalendarItemPopulator;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarItem')]
final class CalendarItemPopulatorTest extends FeatureTestCase
{
    private CalendarItemPopulator $calendarItemPopulator;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->calendarItemPopulator = $this->app->make(CalendarItemPopulator::class);
    }

    public function testPopulate(): void
    {
        $policyVersion = PolicyVersion::factory()->create();

        $result = $this->calendarItemPopulator->populate($policyVersion);

        $this->assertCount(7, $result);
        $this->assertDatabaseCount(CalendarItem::class, $result->count());
        foreach ($result->map->label->toArray() as $label) {
            $this->assertDatabaseHas(CalendarItem::class, ['label' => $label, 'policy_version_uuid' => $policyVersion->uuid]);
        }

        $groups = $result->groupBy('person_type_enum');

        $this->assertEqualsCanonicalizing(
            [PolicyPersonType::index()->value, PolicyPersonType::contact()->value],
            $groups->keys()->toArray(),
        );

        $this->assertCount(4, $groups[PolicyPersonType::index()->value]);
        $this->assertCount(3, $groups[PolicyPersonType::contact()->value]);
    }
}
