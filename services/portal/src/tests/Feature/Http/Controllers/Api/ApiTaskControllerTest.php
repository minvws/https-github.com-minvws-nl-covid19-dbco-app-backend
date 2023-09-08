<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Task\PersonalDetails;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\Dto\PseudoBsn;
use Carbon\CarbonImmutable;
use Illuminate\Testing\Fluent\AssertableJson;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\InformedBy;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_key_exists;
use function array_merge;
use function array_merge_recursive;
use function sprintf;

#[Group('task')]
#[Group('validation')]
final class ApiTaskControllerTest extends FeatureTestCase
{
    public function testThatLastExposureDateHasValidFormatWhenRetrievingTasks(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->createTaskForCase($case, [
            'task_group' => TaskGroup::contact(),
            'date_of_last_exposure' => '2021-06-10',
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/tasks/contact', $case->uuid));
        $response->assertStatus(200);

        $data = $response->json('tasks');
        $this->assertEquals('2021-06-10', $data[0]['dateOfLastExposure']);
    }

    /**
     * @param array<string, mixed> $postData
     */
    #[DataProvider('invalidCreateTaskDataProvider')]
    public function testCreateTask(array $postData, int $expectedStatus): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => CarbonImmutable::today(),
            'date_of_test' => CarbonImmutable::today(),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        if (array_key_exists('dateOfLastExposureModifier', $postData['task'])) {
            $postData['task']['dateOfLastExposure'] = CarbonImmutable::now()
                ->add($postData['task']['dateOfLastExposureModifier'])
                ->format('Y-m-d');
            unset($postData['task']['dateOfLastExposureModifier']);
        }

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/tasks', $case->uuid), $postData);
        $response->assertStatus($expectedStatus);
    }

    /**
     * @return array<string, mixed>
     */
    public static function invalidCreateTaskDataProvider(): array
    {
        $validCreateTaskData = self::validCreateTaskData();
        return [
            'with invalid dateOfLastExposure' => [
                array_merge_recursive(
                    $validCreateTaskData,
                    ['task' => ['dateOfLastExposureModifier' => '1 day']],
                ),
                422,
            ],
            'group missing' => [
                [
                    'task' => [
                        'group' => null,
                        'label' => 'some label',
                    ],
                ],
                422,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validCreateTaskDataProvider(): array
    {
        $validCreateTaskData = self::validCreateTaskData();

        return [
            'minimal' => [$validCreateTaskData, 200],
            'with valid dateOfLastExposure' => [
                array_merge_recursive(
                    $validCreateTaskData,
                    ['task' => ['dateOfLastExposure' => CarbonImmutable::now()->format('Y-m-d')]],
                ),
                200,
            ],
        ];
    }

    public function testTaskGroupWhenCreatingTask(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $this->faker->dateTimeBetween('-1 week'),
            'date_of_test' => $this->faker->dateTimeBetween('-1 week'),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $postData = self::validCreateTaskData();
        $taskGroup = $this->faker->randomElement(TaskGroup::all());
        $postData['task']['group'] = $taskGroup;

        $this->be($user)->postJson(sprintf('/api/cases/%s/tasks', $case->uuid), $postData);

        $this->assertDatabaseHas('task', [
            'uuid' => $case->tasks()->first()->uuid,
            'case_uuid' => $case->uuid,
            'task_group' => $taskGroup->value,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function validCreateTaskData(): array
    {
        return [
            'task' => [
                'group' => TaskGroup::contact()->value,
                'label' => 'create task',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $postData
     */
    #[DataProvider('validUpdateTaskDataProvider')]
    #[DataProvider('invalidUpdateTaskDataProvider')]
    public function testUpdateTask(array $postData, int $expectedStatus): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => CarbonImmutable::today(),
            'date_of_test' => CarbonImmutable::today(),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);
        $postData['task']['uuid'] = $task->uuid;
        $postData['task']['caseUuid'] = $case->uuid;

        $response = $this->be($user)->putJson(sprintf('/api/tasks/%s', $task->uuid), $postData);
        $response->assertStatus($expectedStatus);
    }

    /**
     * @return array<string, mixed>
     */
    public static function validUpdateTaskDataProvider(): array
    {
            $validPartialUpdateTaskData = self::validPartialUpdateTaskData();

        return [
            'minimal' => [$validPartialUpdateTaskData, 200],
            'with label' => [array_merge($validPartialUpdateTaskData, ['task' => ['label' => 'foo']]), 200],
            'with taskContext' => [array_merge($validPartialUpdateTaskData, ['task' => ['taskContext' => 'foo']]), 200],
            'with nature' => [array_merge($validPartialUpdateTaskData, ['task' => ['nature' => 'foo']]), 200],
            'with commumication' => [
                array_merge($validPartialUpdateTaskData, ['task' => ['commumication' => 'foo']]),
                200,
            ],
            'with informedByStaffAt' => [
                array_merge(
                    $validPartialUpdateTaskData,
                    ['task' => ['informedByStaffAt' => CarbonImmutable::now()->toJSON()]],
                ),
                200,
            ],
            'with dateOfLastExposure' => [
                array_merge(
                    $validPartialUpdateTaskData,
                    ['task' => ['dateOfLastExposure' => CarbonImmutable::now()->format('Y-m-d')]],
                ),
                200,
            ],
            'with isSource' => [array_merge($validPartialUpdateTaskData, ['task' => ['isSource' => true]]), 200],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function invalidUpdateTaskDataProvider(): array
    {
        $validPartialUpdateTaskData = self::validPartialUpdateTaskData();

        return [
            // invalid
            'invalid category' => [array_merge($validPartialUpdateTaskData, ['task' => ['category' => 'foo']]), 422],
            'invalid informStatus' => [
                array_merge($validPartialUpdateTaskData, ['task' => ['informStatus' => 'foo']]),
                422,
            ],
            'invalid dateOfLastExposure: not a date' => [
                array_merge($validPartialUpdateTaskData, ['task' => ['dateOfLastExposure' => 'foo']]),
                422,
            ],
            'invalid dateOfLastExposure: wrong date' => [
                array_merge($validPartialUpdateTaskData, [
                    'task' =>
                        ['dateOfLastExposure' => CarbonImmutable::tomorrow()->format('Y-m-d')],
                ]),
                422,
            ],
            'with invalid taskgroup' => [
                array_merge($validPartialUpdateTaskData, ['task' => ['group' => 'foo']]),
                422,
            ],

            // error
            'invalid informedByStaffAt' => [
                array_merge($validPartialUpdateTaskData, ['task' => ['informedByStaffAt' => 'foo']]),
                500,
            ],
        ];
    }

    public function testUpdatePseudoBsn(): void
    {
        $personalDetails = PersonalDetails::newInstanceWithVersion(1);
        $personalDetails->bsnNotes = 'bsn notes';

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'personalDetails' => $personalDetails,
        ]);

        $guid = '9fc3e93e-e24d-4064-5717-7b4b41cb8993';
        $censoredBsn = '******123';
        $letters = 'EJ';
        $pseudoBsnCollection = [
            new PseudoBsn($guid, $censoredBsn, $letters),
        ];
        $organisationExternalId = $organisation->external_id;

        $this->mock(
            BsnRepository::class,
            static function (MockInterface $mock) use ($guid, $pseudoBsnCollection, $organisationExternalId): void {
                $mock->expects('getByPseudoBsnGuid')
                    ->with($guid, $organisationExternalId)
                    ->andReturn($pseudoBsnCollection);
            },
        );

        $response = $this->be($user)->putJson(sprintf('api/tasks/%s/pseudo-bsn', $task->uuid), [
            'pseudoBsnGuid' => $guid,
        ]);

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($guid): AssertableJson {
            return $json
                ->where('task.pseudoBsnGuid', $guid)
                ->etc();
        });

        $indexFragmentResponse = $this->be($user)->getJson(sprintf('/api/tasks/%s/fragments/personalDetails', $task->uuid));
        $indexFragmentResponse->assertJson(
            static function (AssertableJson $json) use ($censoredBsn, $letters): AssertableJson {
                return $json
                    ->where('data.bsnCensored', $censoredBsn)
                    ->where('data.bsnLetters', $letters)
                    ->where('data.bsnNotes', 'bsn notes')
                    ->etc();
            },
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function validPartialUpdateTaskData(): array
    {
        return [
            'task' => [
                'label' => null,
                'taskContext' => null,
                'nature' => null,
                'category' => '3a',
                'communication' => null,
                'informedByStaffAt' => null,
                'dateOfLastExposure' => null,
                'isSource' => null,
                'informStatus' => 'uninformed',
            ],
        ];
    }

    /**
     * This test will ensure that the InformedBy (aka communication) is automatically set depending on the
     * selected category (if present) when creating a task
     */
    #[DataProvider('createTaskInformedByAutoSetProvider')]
    public function testCreateTaskInformedByIsAutoSet(array $taskPayload, string $expectedCommunication): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/tasks', $case->uuid), array_merge_recursive(
            self::validCreateTaskData(),
            ['task' => $taskPayload],
        ));
        $response->assertStatus(200);

        $data = $response->json('task');

        $this->assertEquals($expectedCommunication, $data['communication']);
    }

    public static function createTaskInformedByAutoSetProvider(): array
    {
        return [
            'cat_1' => [["category" => ContactCategory::cat1()], InformedBy::staff()->value],
            'cat_2a' => [["category" => ContactCategory::cat2a()], InformedBy::staff()->value],
            'cat_2b' => [["category" => ContactCategory::cat2b()], InformedBy::staff()->value],
            'cat_3a' => [["category" => ContactCategory::cat3a()], InformedBy::index()->value],
            'cat_3b' => [["category" => ContactCategory::cat3a()], InformedBy::index()->value],
        ];
    }

    /**
     * This test will ensure that the InformedBy (aka communication) is automatically set depending on the
     * selected category (if present) when updating a task
     */
    #[DataProvider('updateTaskInformedByAutoSetProvider')]
    public function testUpdateTaskInformedByIsAutoSet(
        array $taskCurrentState,
        array $taskPayload,
        ?string $expectedCommunication,
    ): void {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, $taskCurrentState);

        $response = $this->be($user)->putJson(sprintf('/api/tasks/%s', $task->uuid), array_merge_recursive(
            ['task' => ['uuid' => $task->uuid]],
            ['task' => $taskPayload],
        ));
        $response->assertStatus(200);

        $data = $response->json('task');

        $this->assertEquals($expectedCommunication, $data['communication']);
    }

    public static function updateTaskInformedByAutoSetProvider(): array
    {
        $now = CarbonImmutable::now();

        return [
            'cat_1' => [['created_at' => $now, "communication" => null], ["category" => ContactCategory::cat1()], InformedBy::staff()->value],
            'cat_2a' => [['created_at' => $now, "communication" => null], ["category" => ContactCategory::cat2a()], InformedBy::staff()->value],
            'cat_2b' => [['created_at' => $now, "communication" => null], ["category" => ContactCategory::cat2b()], InformedBy::staff()->value],
            'cat_3a' => [['created_at' => $now, "communication" => null], ["category" => ContactCategory::cat3a()], InformedBy::index()->value],
            'cat_3b' => [['created_at' => $now, "communication" => null], ["category" => ContactCategory::cat3a()], InformedBy::index()->value],
            'cat_1_no_override' => [['created_at' => $now, "communication" => InformedBy::index()], ["category" => ContactCategory::cat1()], InformedBy::index()->value],
            'cat_2a_no_override' => [['created_at' => $now, "communication" => InformedBy::index()], ["category" => ContactCategory::cat2a()], InformedBy::index()->value],
            'cat_2b_no_override' => [['created_at' => $now, "communication" => InformedBy::index()], ["category" => ContactCategory::cat2b()], InformedBy::index()->value],
            'cat_3a_no_override' => [['created_at' => $now, "communication" => InformedBy::staff()], ["category" => ContactCategory::cat3a()], InformedBy::staff()->value],
            'cat_3b_no_override' => [['created_at' => $now, "communication" => InformedBy::staff()], ["category" => ContactCategory::cat3a()], InformedBy::staff()->value],
        ];
    }

    public function testUpdateTaskWithoutCommunicationAndCategoryShouldWork(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'category' => null,
            'communication' => null,
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/tasks/%s', $task->uuid), [
            'task' => ['uuid' => $task->uuid],
        ]);
        $response->assertStatus(200);
    }

    public function testCreateTaskWithDottedNotationResponse(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/tasks/%s', $task->uuid), [
            'task' => [
                'uuid' => $task->uuid,
                'dateOfLastExposure' => CarbonImmutable::now(),
            ],
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'validationResult' => [
                'warning' => [
                    'failed' => [
                        'task.dateOfLastExposure',
                    ],
                ],
            ],
        ]);
    }

    public function testDeleteTask(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user);

        $response = $this->be($user)->deleteJson(sprintf('/api/tasks/%s', $task->uuid), [
            'task' => ['uuid' => $task->uuid],
        ]);
        $response->assertOk();
    }
}
