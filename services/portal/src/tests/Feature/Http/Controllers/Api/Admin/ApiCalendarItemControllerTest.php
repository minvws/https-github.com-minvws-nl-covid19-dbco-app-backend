<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyVersion;
use App\Services\CalendarItemService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use MinVWS\DBCO\Enum\Models\FixedCalendarPeriod;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('calendarItem')]
final class ApiCalendarItemControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);
    }

    public const RESPONSE_STRUCTURE = [
        'uuid',
        'policyVersionUuid',
        'policyVersionStatus',
        'label',
        'fixedCalendarName',
        'isDeletable',
        'personType',
        'itemType',
        'color',
    ];

    // LIST
    public function testListCalendarItemRequiresAuthentication(): void
    {
        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-item', $this->faker->uuid))
            ->assertUnauthorized();
    }

    public function testListCalendarItemRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $queryPersonType = PolicyPersonType::contact()->value;

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-item?filter[person]=%s', $policyVersion->uuid, $queryPersonType))
            ->assertForbidden();
    }

    public function testListCalendarItem(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $queryPersonType = PolicyPersonType::contact()->value;

        CalendarItem::factory()
            ->count(5)
            ->create([
                'policy_version_uuid' => $policyVersion,
                'person_type_enum' => PolicyPersonType::index(),
            ]);

        $expectedCalendarItems = CalendarItem::factory()
            ->count(5)
            ->create([
                'policy_version_uuid' => $policyVersion,
                'person_type_enum' => PolicyPersonType::contact(),
            ]);

        $response = $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-item?filter[person]=%s', $policyVersion->uuid, $queryPersonType))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure(['*' => self::RESPONSE_STRUCTURE])
            ->assertJsonCount(5);

        $this->assertEqualsCanonicalizing($expectedCalendarItems->map->uuid->toArray(), $response->json('*.uuid'));
        $expectedCalendarItems->each(function (CalendarItem $calendarItem) use ($policyVersion): void {
            $this->assertSame($policyVersion->uuid, $calendarItem->policy_version_uuid);
            $this->assertSame(PolicyPersonType::contact(), $calendarItem->person_type_enum);
        });
    }

    public function testListCalendarItemWithNoFilter(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-item', $policyVersion->uuid))
            ->assertOk();
    }

    public function testListCalendarItemWithInvalidFilter(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $queryPersonType = $this->faker->word();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-item?filter[person]=%s', $policyVersion->uuid, $queryPersonType))
            ->assertBadRequest()
            ->assertJsonValidationErrors(
                ['filter.person' => 'Query filter parameter "person" is invalid! Allowed values are: "index", "contact".'],
            );
    }

    // GET
    public function testGetCalendarItemRequiresAuthentication(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertUnauthorized();
    }

    public function testGetCalendarItemRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertForbidden();
    }

    public function testGetCalendarItem(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->getJson(sprintf('/api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE)
            ->assertJsonFragment([
                'uuid' => $calendarItem->uuid,
                'policyVersionUuid' => $policyVersion->uuid,
            ]);
    }

    public function testGetCalendarItemReturnsNotFound(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->get(sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $this->faker->uuid()))
            ->assertNotFound();
    }

    // DELETE
    public function testDeleteCalendarItemRequiresAuthentication(): void
    {
        $this
            ->deleteJson(sprintf('/api/admin/policy-version/%s/calendar-item/%s', $this->faker->uuid, $this->faker->uuid))
            ->assertUnauthorized();
    }

    public function testDeleteCalendarItemRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->deleteJson(sprintf('/api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertForbidden();
    }

    public function testDeleteCalendarItem(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create(['fixed_calendar_item_enum' => null]);

        $this
            ->deleteJson(sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertNoContent();

        $this->assertDatabaseMissing($calendarItem->getTable(), ['uuid' => $calendarItem->uuid]);
    }

    public function testDeleteCalendarItemReturnsOnNonExistingCalendarItem(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);

        $this
            ->deleteJson(sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $this->faker->uuid()))
            ->assertNotFound();
    }

    public function testDeleteCalendarItemReturns404OnFailure(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        /** @var CalendarItemService&MockInterface $calendarItemService */
        $calendarItemService = Mockery::mock(CalendarItemService::class);
        $this->app->instance(CalendarItemService::class, $calendarItemService);

        $calendarItemService
            ->shouldReceive('deleteCalendarItem')
            ->once()
            ->with(Mockery::type(CalendarItem::class))
            ->andReturn(false);

        $this
            ->deleteJson(sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertNotFound();
    }

    public function testDeleteCalendarItemReturnsErrorOnNonDeletableCalendarItem(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create(['fixed_calendar_item_enum' => FixedCalendarPeriod::source()]);

        $this
            ->deleteJson(sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors(['general' => 'This calendar item cannot be deleted!']);

        $this->assertDatabaseHas($calendarItem->getTable(), ['uuid' => $calendarItem->uuid]);
    }

    // CREATE
    public function testCreateCalendarItemRequiresAuthentication(): void
    {
        $this
            ->postJson(sprintf('/api/admin/policy-version/%s/calendar-item', $this->faker->uuid))
            ->assertUnauthorized();
    }

    public function testCreateCalendarItemRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);

        $this
            ->postJson(sprintf('/api/admin/policy-version/%s/calendar-item', $policyVersion->uuid))
            ->assertForbidden();
    }

    public function testCreateCalendarItem(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);

        $uuid = $this
            ->postJson(
                sprintf('api/admin/policy-version/%s/calendar-item', $policyVersion->uuid),
                [
                    'label' => $this->faker->words(asText: true),
                    'personType' => PolicyPersonType::index()->value,
                    'itemType' => CalendarItemEnum::point()->value,
                    'color' => CalendarPointColor::red(),
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE)
            ->assertJsonFragment([
                'policyVersionUuid' => $policyVersion->uuid,
            ])
            ->json('uuid');

        $this->assertDatabaseHas(CalendarItem::class, ['uuid' => $uuid, 'policy_version_uuid' => $policyVersion->uuid]);
    }

    #[DataProvider('getCreateCalendarItemData')]
    public function testCreateCalendarItemReturnsValidationErrorsOnIncorrectRequest(array $requestData, array $expectedValidationErrors): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);

        $this
            ->postJson(sprintf('api/admin/policy-version/%s/calendar-item', $policyVersion->uuid), $requestData)
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors($expectedValidationErrors);
    }

    public static function getCreateCalendarItemData(): array
    {
        return [
            'all fields are required' => [
                'requestData' => [],
                'expectedValidationErrors' => [
                    'label' => ['Veld "Label" is verplicht.'],
                    'personType' => ['Veld "Person type" is verplicht.'],
                    'itemType' => ['Veld "Item type" is verplicht.'],
                    'color' => ['Veld "Color" is verplicht.'],
                ],
            ],
            'invalid person type' => [
                'requestData' => [
                    'label' => 'lorum ipsum',
                    'personType' => 'lorum',
                    'itemType' => CalendarItemEnum::point()->value,
                    'color' => CalendarPointColor::red(),
                ],
                'expectedValidationErrors' => [
                    'personType' => ['Veld "Person type" is ongeldig.'],
                ],
            ],
            'invalid item type' => [
                'requestData' => [
                    'label' => 'lorum ipsum',
                    'personType' => PolicyPersonType::index()->value,
                    'itemType' => 'lorum',
                    'color' => CalendarPointColor::red(),
                ],
                'expectedValidationErrors' => [
                    'itemType' => ['Veld "Item type" is ongeldig.'],
                ],
            ],
            'invalid color' => [
                'requestData' => [
                    'label' => 'lorum ipsum',
                    'personType' => PolicyPersonType::index()->value,
                    'itemType' => CalendarItemEnum::period()->value,
                    'color' => 'lorum ipsum',
                ],
                'expectedValidationErrors' => [
                    'color' => ['Veld "Color" is ongeldig.'],
                ],
            ],
            'invalid item/color combination' => [
                'requestData' => [
                    'label' => 'lorum ipsum',
                    'personType' => PolicyPersonType::index()->value,
                    'itemType' => CalendarItemEnum::period()->value,
                    'color' => CalendarPointColor::red(),
                ],
                'expectedValidationErrors' => [
                    'color' => ['Ongeldige kleur/"Calendar item" combinatie!'],
                ],
            ],
        ];
    }

    // UPDATE
    public function testUpdateCalendarItemRequiresAuthentication(): void
    {
        $this
            ->putJson(sprintf('api/admin/policy-version/%s/calendar-item/%s', $this->faker->uuid(), $this->faker->uuid()))
            ->assertUnauthorized();
    }

    public function testUpdateCalendarItemRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->putJson(sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertForbidden();
    }

    public function testUpdateCalendarItem(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create([
                'calendar_item_enum' => CalendarItemEnum::point(),
                'color_enum' => CalendarPointColor::red(),
            ]);

        $response = $this
            ->putJson(
                sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid),
                $requestData = [
                    'label' => $this->faker->words(asText: true),
                    'color' => $this->faker->randomElement(CalendarPointColor::allValues()),
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

            $this->assertSame($requestData['label'], $response->json('label'));
            $this->assertSame($requestData['color'], $response->json('color'));
            $this->assertDatabaseHas(
                CalendarItem::class,
                [
                    'uuid' => $response->json('uuid'),
                    'label' => $response->json('label'),
                    'color_enum' => $response->json('color'),
                ],
            );
    }

    public function testUpdateCalendarItemWithoutSendingAnyData(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        $response = $this
            ->putJson(sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas(CalendarItem::class, ['uuid' => $response->json('uuid'), 'label' => $response->json('label')]);
    }

    #[DataProvider('getUpdateCalendarItemData')]
    public function testUpdateCalendarItemReturnsValidationErrorsOnIncorrectRequest(array $requestData, array $expectedValidationErrors): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->putJson(sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid), $requestData)
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors($expectedValidationErrors);
    }

    public function testUpdateCalendarItemReturnsValidationErrorWhenColorAndCalendarItemDoNotMatch(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create([
                'calendar_item_enum' => CalendarItemEnum::point(),
            ]);

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid),
                [
                    'color' => CalendarPeriodColor::lightRed(),
                ],
            )
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors([
                'color' => ['Ongeldige kleur/"Calendar item" combinatie!'],
            ]);
    }

    #[DataProvider('getAllPolicyVersionStatusExceptDraft')]
    public function testUpdateCalendarItemsThrowsValidationExceptionIfPolicyVersionStatusIsNotDraft(PolicyVersionStatus $status): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create(['status' => $status]);
        $calendarItem = CalendarItem::factory()
            ->recycle($policyVersion)
            ->create();

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid),
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

    public static function getUpdateCalendarItemData(): array
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
            'invalid color' => [
                'requestData' => [
                    'color' => 'lorum ipsum',
                ],
                'expectedValidationErrors' => [
                    'color' => ['Veld "Color" is ongeldig.'],
                ],
            ],
            'label is to short and invalid color' => [
                'requestData' => [
                    'label' => 'A',
                    'color' => 'lorum ipsum',
                ],
                'expectedValidationErrors' => [
                    'label' => ['Veld "Label" moet minimaal 2 tekens zijn.'],
                    'color' => ['Veld "Color" is ongeldig.'],
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
