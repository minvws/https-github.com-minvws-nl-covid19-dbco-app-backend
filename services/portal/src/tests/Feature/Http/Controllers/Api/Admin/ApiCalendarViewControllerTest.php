<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarView;
use App\Models\Policy\PolicyVersion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\CalendarView as CalendarViewEnum;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('calendarView')]
final class ApiCalendarViewControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);
    }

    public const RESPONSE_STRUCTURE = [
        'uuid',
        'policyVersionUuid',
        'label',
        'calendarViewEnum',
        'calendarItems',
    ];

    // LIST
    public function testListCalendarViewRequiresAuthentication(): void
    {
        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-view', $this->faker->uuid))
            ->assertUnauthorized();
    }

    public function testListCalendarViewRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-view', $policyVersion->uuid))
            ->assertForbidden();
    }

    public function testListCalendarViews(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        /** @var Collection $expectedCalendarViews */
        $expectedCalendarViews = CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion)
            ->count(5)
            ->create();

        $response = $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-view', $policyVersion->uuid))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure(['*' => self::RESPONSE_STRUCTURE])
            ->assertJsonCount(5);

        $this->assertEqualsCanonicalizing($expectedCalendarViews->map->uuid->toArray(), $response->json('*.uuid'));
        $expectedCalendarViews->each(function (CalendarView $calendarView) use ($policyVersion): void {
            $this->assertSame($policyVersion->uuid, $calendarView->policy_version_uuid);
        });
    }

    public function testGetCalendarView(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion, [], 5)
            ->create();

        $response = $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $calendarView->uuid));

        $response->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE)
            ->assertJsonFragment([
                'uuid' => $calendarView->uuid,
                'policyVersionUuid' => $policyVersion->uuid,
            ]);
    }

    public function testGetCalendarViewReturnsNotFound(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->get(sprintf('api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $this->faker->uuid()))
            ->assertNotFound();
    }

    // UPDATE
    public function testUpdateCalendarView(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexSidebar(),
            ]);

        $response = $this
            ->putJson(
                sprintf('api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $calendarView->uuid),
                $requestData = [
                    'label' => $this->faker->words(asText: true),
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

            $this->assertSame($requestData['label'], $response->json('label'));
            $this->assertDatabaseHas(
                CalendarView::class,
                [
                    'uuid' => $response->json('uuid'),
                    'label' => $response->json('label'),
                ],
            );
    }

    public function testUpdateCalendarViewWithAssociativeCalendarItemArray(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);

        $calendarItems = CalendarItem::factory()
            ->recycle($policyVersion)
            ->count(4)
            ->create();

        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexSidebar(),
            ]);

        $response = $this
            ->putJson(
                sprintf('api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $calendarView->uuid),
                $requestData = [
                    'label' => $this->faker->words(asText: true),
                    'calendarItems' => $calendarItems->map(
                        static fn(CalendarItem $calendarItem) => ['uuid' => $calendarItem->uuid]
                    )->toArray(),
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

            $this->assertSame($requestData['label'], $response->json('label'));
            $this->assertDatabaseHas(
                CalendarView::class,
                [
                    'uuid' => $response->json('uuid'),
                    'label' => $response->json('label'),
                ],
            );
    }

    public function testUpdateCalendarViewWithoutSendingAnyData(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->create();

        $response = $this
            ->putJson(sprintf('api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $calendarView->uuid))
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas(CalendarView::class, ['uuid' => $response->json('uuid'), 'label' => $response->json('label')]);
    }

    #[DataProvider('getUpdateCalendarViewData')]
    public function testUpdateCalendarViewReturnsValidationErrorsOnIncorrectRequest(array $requestData, array $expectedValidationErrors): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->putJson(sprintf('api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $calendarView->uuid), $requestData)
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors($expectedValidationErrors);
    }

    public function testUpdateCalendarViewReturnsValidationErrorWhenColorAndCalendarViewDoNotMatch(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexSidebar(),
            ]);

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $calendarView->uuid),
                [
                    'label' => 'd',
                ],
            )
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors([
                'label' => ['Veld "Label" moet minimaal 2 tekens zijn.'],
            ]);
    }

    #[DataProvider('getAllPolicyVersionStatusExceptDraft')]
    public function testUpdateCalendarViewsThrowsValidationExceptionIfPolicyVersionStatusIsNotDraft(PolicyVersionStatus $status): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => $status]);
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $calendarView->uuid),
                [
                    'label' => $this->faker->words(asText: true),
                ],
            )
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors([
                'policyVersion' => ['Changes are not allowed unless status is on draft.'],
            ]);
    }

    #[DataProvider('getUpdateCalendarViewDataRemovingAllCalendarItems')]
    public function testUpdateCalendarViewRemovingAllCalendarItems(?array $calendarItems): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexSidebar(),
            ]);

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s/calendar-view/%s', $policyVersion->uuid, $calendarView->uuid),
                [
                    'calendarItems' => $calendarItems,
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseMissing('calendar_view_calendar_item', [
            'calendar_view_uuid' => $calendarView->uuid,
        ]);
    }

    public static function getUpdateCalendarViewDataRemovingAllCalendarItems(): array
    {
        return [
            'null' => [null],
            'empty array' => [[]],
        ];
    }

    public static function getUpdateCalendarViewData(): array
    {
        return [
            'label is to short' => [
                'requestData' => [
                    'label' => 'A',
                ],
                'expectedValidationErrors' => [
                    'label' => ['Veld "Label" moet minimaal 2 tekens zijn.'],
                ],
            ],
        ];
    }

    public static function getAllPolicyVersionStatusExceptDraft(): array
    {
        return Collection::make(PolicyVersionStatus::all())
            ->reject(static fn (PolicyVersionStatus $status) => $status === PolicyVersionStatus::draft())
            ->mapWithKeys(
                static fn (PolicyVersionStatus $status): array => [sprintf('status "%s"', $status->value) => ['status' => $status]],
            )
            ->toArray();
    }
}
