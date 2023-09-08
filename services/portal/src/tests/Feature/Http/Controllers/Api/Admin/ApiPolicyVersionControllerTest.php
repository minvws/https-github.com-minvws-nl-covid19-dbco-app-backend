<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyVersion;
use App\Services\PolicyVersionService;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_key_exists;
use function sprintf;

#[Group('policy')]
#[Group('policyVersion')]
class ApiPolicyVersionControllerTest extends FeatureTestCase
{
    public const DEFAULT_ENCODER_CONTEXT_DATE_FORMAT = 'Y-m-d\TH:i:sp';

    public const RESPONSE_STRUCTURE = [
        'uuid',
        'name',
        'status',
        'startDate',
    ];

    // LIST
    public function testListPolicyVersionRequiresAuthentication(): void
    {
        $this
            ->getJson('api/admin/policy-version')
            ->assertUnauthorized();
    }

    public function testListPolicyVersionRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $this
            ->getJson('api/admin/policy-version')
            ->assertForbidden();
    }

    public function testListPolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersionOne = PolicyVersion::factory()->create();
        $policyVersionTwo = PolicyVersion::factory()->create();
        $policyVersionThree = PolicyVersion::factory()->create();

        $response = $this
            ->getJson('api/admin/policy-version')
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure(['*' => self::RESPONSE_STRUCTURE])
            ->assertJsonCount(3);

        $uuids = $response->json('*.uuid');

        $this->assertContains($policyVersionOne->uuid, $uuids);
        $this->assertContains($policyVersionTwo->uuid, $uuids);
        $this->assertContains($policyVersionThree->uuid, $uuids);
    }

    public function testListPolicyVersionAutoPopulates(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $this->assertDatabaseCount('policy_version', 0);

        $this
            ->getJson('api/admin/policy-version')
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure(['*' => self::RESPONSE_STRUCTURE])
            ->assertJsonCount(1);

        $this->assertDatabaseCount('policy_version', 1);
        Event::assertDispatched(PolicyVersionCreated::class);
    }

    // GET
    public function testGetPolicyVersionRequiresAuthentication(): void
    {
        $this
            ->getJson(sprintf('api/admin/policy-version/%s', $this->faker->uuid()))
            ->assertUnauthorized();
    }

    public function testGetPolicyVersionRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->getJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid))
            ->assertForbidden();
    }

    public function testGetPolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->getJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid))
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE)
            ->assertJsonFragment([
                'uuid' => $policyVersion->uuid,
            ]);
    }

    // DELETE
    public function testDeletePolicyVersionRequiresAuthentication(): void
    {
        $this
            ->deleteJson(sprintf('api/admin/policy-version/%s', $this->faker->uuid()))
            ->assertUnauthorized();
    }

    public function testDeletePolicyVersionRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->deleteJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid))
            ->assertForbidden();
    }

    public function testDeletePolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this->assertDatabaseHas(PolicyVersion::class, ['uuid' => $policyVersion->uuid]);

        $this
            ->delete(sprintf('api/admin/policy-version/%s', $policyVersion->uuid))
            ->assertNoContent();
    }

    public function testDeletePolicyVersionReturnsOnNonExistingPolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $this
            ->delete(sprintf('api/admin/policy-version/%s', $this->faker->uuid()))
            ->assertNotFound();
    }

    public function testDeletePolicyVersionReturns404OnFailure(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this->assertDatabaseHas(PolicyVersion::class, ['uuid' => $policyVersion->uuid]);

        /** @var PolicyVersionService&MockInterface $policyVersionService */
        $policyVersionService = Mockery::mock(PolicyVersionService::class);
        $this->app->instance(PolicyVersionService::class, $policyVersionService);

        $policyVersionService
            ->shouldReceive('deletePolicyVersion')
            ->once()
            ->with(Mockery::type(PolicyVersion::class))
            ->andReturn(false);

        $this
            ->delete(sprintf('api/admin/policy-version/%s', $policyVersion->uuid))
            ->assertNotFound();
    }

    // CREATE
    public function testCreatePolicyVersionRequiresAuthentication(): void
    {
        $this
            ->postJson('api/admin/policy-version')
            ->assertUnauthorized();
    }

    public function testCreatePolicyVersionRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $this
            ->postJson('api/admin/policy-version')
            ->assertForbidden();
    }

    public function testCreatePolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $response = $this
            ->postJson(
                'api/admin/policy-version?foobar=yup',
                $requestData = [
                    'name' => 'My name',
                    'startDate' => $this->faker
                        ->dateTimeBetween('now + 1 month', '+1 year')
                        ->format(self::DEFAULT_ENCODER_CONTEXT_DATE_FORMAT),
                ],
            )
            ->assertCreated()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

            $this->assertSame($requestData['name'], $response->json('name'));
            $this->assertSame($requestData['startDate'], $response->json('startDate'));
            $this->assertDatabaseHas(
                PolicyVersion::class,
                [
                    'uuid' => $response->json('uuid'),
                    'status' => $response->json('status'),
                    'start_date' => $response->json('startDate'),
                ],
            );
    }

    #[DataProvider('getCreatePolicyVersionData')]
    public function testCreatePolicyVersionReturnsValidationErrorsOnIncorrectRequest(
        array $requestData,
        array $expectedValidationErrors,
    ): void {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        if (array_key_exists('startDateModifier', $requestData)) {
            $requestData['startDate'] = CarbonImmutable::now()
                ->add($requestData['startDateModifier'])
                ->toAtomString();
            unset($requestData['startDateModifier']);
        }

        $this
            ->postJson('api/admin/policy-version', $requestData)
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors($expectedValidationErrors);
    }

    public static function getCreatePolicyVersionData(): array
    {
        return [
            'start date is required' => [
                'requestData' => [
                    'name' => 'lorum',
                ],
                'expectedValidationErrors' => [
                    'startDate' => ['Veld "Startdatum" is verplicht'],
                ],
            ],
            'start date should be after or equal today' => [
                'requestData' => [
                    'name' => 'lorum',
                    'startDateModifier' => '-1 day',
                ],
                'expectedValidationErrors' => [
                    'startDate' => ['Deze datum mag niet in het verleden liggen.'],
                ],
            ],
            'name is to short' => [
                'requestData' => [
                    'name' => 'A',
                    'startDateModifier' => '1 day',
                ],
                'expectedValidationErrors' => [
                    'name' => ['Veld "Naam" moet minimaal 2 tekens zijn'],
                ],
            ],
            'no data send' => [
                'requestData' => [],
                'expectedValidationErrors' => [
                    'name' => ['"Naam" is verplicht.'],
                    'startDate' => ['Veld "Startdatum" is verplicht'],
                ],
            ],
        ];
    }

    public function testCreatePolicyVersionValidationUniqueStartDate(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::today(),
        ]);

        $requestData = [
            'name' => $this->faker->word(),
            'startDate' => CarbonImmutable::now()->toAtomString(),
        ];

        $this
            ->postJson('api/admin/policy-version', $requestData)
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors(['startDate' => ['Op deze datum wordt al een andere beleidsversie geactiveerd']]);
    }

    // UPDATE
    public function testUpdatePolicyVersionRequiresAuthentication(): void
    {
        $this
            ->putJson(sprintf('api/admin/policy-version/%s', $this->faker->uuid()))
            ->assertUnauthorized();
    }

    public function testUpdatePolicyVersionRequiresAuthorization(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->putJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid))
            ->assertForbidden();
    }

    public function testUpdatePolicyVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
        ]);

        $response = $this
            ->putJson(
                sprintf('api/admin/policy-version/%s', $policyVersion->uuid),
                $requestData = [
                    'name' => $this->faker->words(asText: true),
                    'status' => PolicyVersionStatus::draft()->value,
                    'startDate' => $this->faker
                        ->dateTimeBetween('now + 1 month', '+1 year')
                        ->format(self::DEFAULT_ENCODER_CONTEXT_DATE_FORMAT),
                ],
            )
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertSame($requestData['name'], $response->json('name'));
        $this->assertSame($requestData['status'], $response->json('status'));
        $this->assertDatabaseHas(
            PolicyVersion::class,
            [
                'uuid' => $response->json('uuid'),
                'name' => $response->json('name'),
                'status' => $response->json('status'),
            ],
        );
    }

    public function testUpdatePolicyVersionWithoutSendingAnyData(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create();

        $response = $this
            ->putJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid))
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);

        $this->assertDatabaseHas(
            PolicyVersion::class,
            [
                'uuid' => $response->json('uuid'),
                'name' => $response->json('name'),
                'status' => $response->json('status'),
            ],
        );
    }

    public function testUpdatePolicyVersionNotAllowedWhenStatusIsNotDraft(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
        ]);

        $requestData = [
            'name' => 'UPDATED NAME',
        ];

        $this->putJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid), $requestData)
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Changes are not allowed unless status is on draft.']);
    }

    public function testUpdatePolicyVersionAllowPropertyChangeWhenStatusIsDraft(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
        ]);

        $requestData = [
            'name' => 'UPDATED NAME',
            'status' => PolicyVersionStatus::draft(),
        ];

        $this->putJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid), $requestData)
            ->assertOk();
    }

    #[DataProvider('getUpdatePolicyVersionData')]
    public function testUpdatePolicyVersionReturnsValidationErrorsOnIncorrectRequest(array $requestData, array $expectedValidationErrors): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        if (array_key_exists('startDateModifier', $requestData)) {
            $requestData['startDate'] = CarbonImmutable::now()
                ->add($requestData['startDateModifier'])
                ->toAtomString();
            unset($requestData['startDateModifier']);
        }

        $policyVersion = PolicyVersion::factory()->create();

        $this
            ->putJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid), $requestData)
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors($expectedValidationErrors);
    }

    public static function getUpdatePolicyVersionData(): array
    {
        return [
            'start date should be equal to today or later' => [
                'requestData' => [
                    'startDateModifier' => '-1 days',
                ],
                'expectedValidationErrors' => [
                    'startDate' => ['Deze datum mag niet in het verleden liggen.'],
                ],
            ],
            'name is to short' => [
                'requestData' => [
                    'name' => 'A',
                ],
                'expectedValidationErrors' => [
                    'name' => ['Veld "Naam" moet minimaal 2 tekens zijn'],
                ],
            ],
            'status is to short' => [
                'requestData' => [
                    'name' => 'A',
                ],
                'expectedValidationErrors' => [
                    'name' => ['Veld "Naam" moet minimaal 2 tekens zijn'],
                ],
            ],
        ];
    }

    public function testUpdatePolicyVersionStatusWithUnknownStatusShouldFail(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::tomorrow(),
        ]);

        $this->putJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid), [
            'status' => 'unknown',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status' => 'Veld "Status" is ongeldig']);
    }

    #[DataProvider('successfulPolicyVersionStatusTransitions')]
    #[DataProvider('unchangedPolicyVersionStatusTransitions')]
    public function testUpdatePolicyVersionStatusSuccess(
        PolicyVersionStatus $initialStatus,
        PolicyVersionStatus $newStatus,
        int $addStartDateDays,
    ): void {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        // Active PolicyVersion
        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::yesterday(),
        ]);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => $initialStatus,
            'start_date' => CarbonImmutable::now()->addDays($addStartDateDays),
        ]);

        $response = $this->putJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid), [
            'status' => $newStatus->value,
        ]);

        $response
            ->assertSuccessful()
            ->assertJsonStructure(self::RESPONSE_STRUCTURE);
    }

    public static function successfulPolicyVersionStatusTransitions(): array
    {
        return [
            'draft to active-soon' => [PolicyVersionStatus::draft(), PolicyVersionStatus::activeSoon(), 3],
            'active-soon to draft' => [PolicyVersionStatus::activeSoon(), PolicyVersionStatus::draft(), 3],
            'draft to active on current day' => [PolicyVersionStatus::draft(), PolicyVersionStatus::active(), 0],
        ];
    }

    public static function unchangedPolicyVersionStatusTransitions(): Generator
    {
        foreach (PolicyVersionStatus::all() as $status) {
            yield $status->value => [$status, $status, 0];
        }
    }

    #[DataProvider('failingPolicyVersionStatusTransitions')]
    public function testUpdatePolicyVersionStatusFailing(
        PolicyVersionStatus $initialStatus,
        PolicyVersionStatus $newStatus,
        int $addStartDateDays,
    ): void {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => $initialStatus,
            'start_date' => CarbonImmutable::now()->addDays($addStartDateDays),
        ]);

        $this
            ->putJson(sprintf('api/admin/policy-version/%s', $policyVersion->uuid), [
                'status' => $newStatus->value,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status' => 'Invalid status transition']);
    }

    public static function failingPolicyVersionStatusTransitions(): array
    {
        return [
            'draft to old' => [
                'initialStatus' => PolicyVersionStatus::draft(),
                'newStatus' => PolicyVersionStatus::old(),
                'startDateModifier' => 3,
            ],
            'draft to active' => [
                'initialStatus' => PolicyVersionStatus::draft(),
                'newStatus' => PolicyVersionStatus::active(),
                'startDateModifier' => 3,
            ],
            'draft to active-soon' => [ // Not allowed on current day
                'initialStatus' => PolicyVersionStatus::draft(),
                'newStatus' => PolicyVersionStatus::activeSoon(),
                'startDateModifier' => 0,
            ],
            'active to draft' => [
                'initialStatus' => PolicyVersionStatus::active(),
                'newStatus' => PolicyVersionStatus::draft(),
                'startDateModifier' => 3,
            ],
            'activeSoon to old' => [
                'initialStatus' => PolicyVersionStatus::activeSoon(),
                'newStatus' => PolicyVersionStatus::old(),
                'startDateModifier' => 3,
            ],
            'old to active' => [
                'initialStatus' => PolicyVersionStatus::old(),
                'newStatus' => PolicyVersionStatus::active(),
                'startDateModifier' => 3,
            ],
            'old to active-soon' => [
                'initialStatus' => PolicyVersionStatus::old(),
                'newStatus' => PolicyVersionStatus::activeSoon(),
                'startDateModifier' => 3,
            ],
            'old to draft' => [
                'initialStatus' => PolicyVersionStatus::old(),
                'newStatus' => PolicyVersionStatus::draft(),
                'startDateModifier' => 3,
            ],
        ];
    }

    public function testUpdatePolicyVersionValidationUniqueStartDate(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::today(),
        ]);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
            'start_date' => CarbonImmutable::now()->addDays(3),
        ]);

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s', $policyVersion->uuid),
                [
                    'startDate' => $this->faker
                        ->dateTimeBetween(CarbonImmutable::today()->startOfDay(), CarbonImmutable::today()->endOfDay())
                        ->format(self::DEFAULT_ENCODER_CONTEXT_DATE_FORMAT),
                ],
            )
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors(['startDate' => ['Op deze datum wordt al een andere beleidsversie geactiveerd']]);
    }

    public function testUpdatePolicyVersionValidationUniqueStartDateWhenAllowedStatesExistsOnSameDay(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
            'start_date' => CarbonImmutable::today(),
        ]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::today(),
        ]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::old(),
            'start_date' => CarbonImmutable::today(),
        ]);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
            'start_date' => CarbonImmutable::now()->addDays(3),
        ]);

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s', $policyVersion->uuid),
                [
                    'startDate' => $this->faker
                        ->dateTimeBetween(CarbonImmutable::today()->startOfDay(), CarbonImmutable::today()->endOfDay())
                        ->format(self::DEFAULT_ENCODER_CONTEXT_DATE_FORMAT),
                ],
            )
            ->assertOk();
    }

    public function testUpdatePolicyVersionToActiveOnCurrentDay(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::old(),
            'start_date' => CarbonImmutable::today(),
        ]);

        $activePolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::today(),
        ]);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
            'start_date' => CarbonImmutable::today(),
        ]);

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s', $policyVersion->uuid),
                [
                    'status' => PolicyVersionStatus::active(),
                ],
            )
            ->assertOk();

        $this->assertDatabaseHas(PolicyVersion::class, [
            'uuid' => $activePolicyVersion->uuid,
            'status' => PolicyVersionStatus::old(),
        ]);
    }

    public function testUpdatePolicyVersionToActiveOnCurrentDayWithoutActiveVersion(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'admin');
        $this->be($user);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::old(),
            'start_date' => CarbonImmutable::today(),
        ]);

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
            'start_date' => CarbonImmutable::today(),
        ]);

        $this
            ->putJson(
                sprintf('api/admin/policy-version/%s', $policyVersion->uuid),
                [
                    'status' => PolicyVersionStatus::active(),
                ],
            )
            ->assertOk();

        $this->assertDatabaseHas(PolicyVersion::class, [
            'uuid' => $policyVersion->uuid,
            'status' => PolicyVersionStatus::active(),
        ]);
    }
}
