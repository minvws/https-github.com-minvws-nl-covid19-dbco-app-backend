<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Requests\Api\ExpertQuestion\FindByCaseIdRequest;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\FeatureTestCase;

use function collect;
use function config;
use function count;
use function sprintf;
use function usort;

final class ApiExpertQuestionSupervisorsControllerTest extends FeatureTestCase
{
    private const PATH_FIND_BY_CASE_ID = '/api/expert-questions/find-by-case-id';

    public function testListExpertQuestionsPagination(): void
    {
        // given multiple questions
        $organisation = $this->createOrganisation();
        $expectQuestionIds = [];
        for ($i = 0; $i < 2; $i++) {
            $question = $this->createExpertQuestionForCase(
                $this->createCaseForOrganisation($organisation),
                ['type' => ExpertQuestionType::medicalSupervision()],
            );
            $expectQuestionIds[] = $question->uuid;
        }

        // when fetching them page for page
        $returnedExpertQuestionIds = [];
        $supervisor = $this->createUserForOrganisation($organisation, [], 'medical_supervisor');
        for ($i = 1; $i < 3; $i++) {
            $response = $this->be($supervisor)->get(sprintf('api/expert-questions?type=medical-supervision&page=%d&perPage=1', $i));
            $returnedExpertQuestionIds[] = $response->json('data.0.uuid');
        }
        // then all the questions should be returned
        $this->assertEqualsCanonicalizing($expectQuestionIds, $returnedExpertQuestionIds);
    }

    public function testListExpertQuestionsSortAndOrder(): void
    {
        $organisation = $this->createOrganisation();
        $supervisor = $this->createUserForOrganisation($organisation, [], 'medical_supervisor');

        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), [
            'uuid' => '1000001',
            'type' => ExpertQuestionType::medicalSupervision(),
            'created_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => $supervisor->uuid,
        ]);
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), [
            'uuid' => '1000002',
            'type' => ExpertQuestionType::medicalSupervision(),
            'created_at' => CarbonImmutable::now()->subDays(1),
        ]);
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), [
            'uuid' => '1000003',
            'type' => ExpertQuestionType::medicalSupervision(),
            'created_at' => CarbonImmutable::now()->subDays(2),
        ]);
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), [
            'uuid' => '1000004',
            'type' => ExpertQuestionType::medicalSupervision(),
            'created_at' => CarbonImmutable::now()->subDays(3),
            'assigned_user_uuid' => $supervisor->uuid,
        ]);
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), [
            'uuid' => '1000005',
            'type' => ExpertQuestionType::medicalSupervision(),
            'created_at' => CarbonImmutable::now()->subDays(4),
        ]);

        $response = $this->be($supervisor)->getJson('api/expert-questions?type=medical-supervision');
        $sortedExpertQuestionUuids = collect($response->json('data'))->pluck('uuid')->toArray();
        $this->assertEquals(['1000005', '1000003', '1000002', '1000004', '1000001'], $sortedExpertQuestionUuids);

        $response = $this->be($supervisor)->getJson('api/expert-questions?type=medical-supervision&sort=createdAt&order=desc');
        $sortedExpertQuestionUuids = collect($response->json('data'))->pluck('uuid')->toArray();
        $this->assertEquals(['1000001', '1000002', '1000003', '1000004', '1000005'], $sortedExpertQuestionUuids);

        $response = $this->be($supervisor)->getJson('api/expert-questions?type=medical-supervision&sort=status&order=desc');
        $sortedExpertQuestionUuids = collect($response->json('data'))->pluck('uuid')->toArray();
        $this->assertEquals(['1000004', '1000001', '1000005', '1000003', '1000002'], $sortedExpertQuestionUuids);
    }

    public static function expertQuestionRolesDataProvider(): Generator
    {
        yield 'Medical Supervisor' => [
            'medical_supervisor', // User role
            ExpertQuestionType::medicalSupervision(), // Enum type
            ExpertQuestionType::conversationCoach(), // opposite enum type
        ];

        yield 'Conversation Coach' => [
            'conversation_coach', // User role
            ExpertQuestionType::conversationCoach(), // Enum type
            ExpertQuestionType::medicalSupervision(), // opposite enum type
        ];
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testListExpertQuestions(string $userRole, ExpertQuestionType $expertQuestionType): void
    {
        // GIVEN that we are the user
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $userRole);
        $this->be($user);

        // GIVEN that there are multiple questions for medical supervisor
        $expertQuestions = [
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
        ];

        // DO sort the array of expert questions as how they will be set within the response
        usort($expertQuestions, static function ($a, $b) {
            return $a->created_at > $b->created_at;
        });

        // ASSERT that they are listed within the response
        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'from' => 1,
            'to' => 3,
            'total' => 3,
            'currentPage' => 1,
            'lastPage' => 1,
            'data' => [
                [
                    'uuid' => $expertQuestions[0]->uuid,
                    'caseUuid' => $expertQuestions[0]->case_uuid,
                    'user' => [
                        'name' => $expertQuestions[0]->user->name,
                        'roles' => $expertQuestions[0]->user->getRolesArray(),
                        'uuid' => $expertQuestions[0]->user->uuid,
                    ],
                    'assignedUser' => $expertQuestions[0]->assigned_user_uuid,
                    'type' => $expertQuestions[0]->type->value,
                    'subject' => $expertQuestions[0]->subject,
                    'phone' => $expertQuestions[0]->phone,
                    'question' => $expertQuestions[0]->question,
                ],
                [
                    'uuid' => $expertQuestions[1]->uuid,
                    'caseUuid' => $expertQuestions[1]->case_uuid,
                    'user' => [
                        'name' => $expertQuestions[1]->user->name,
                        'roles' => $expertQuestions[1]->user->getRolesArray(),
                        'uuid' => $expertQuestions[1]->user->uuid,
                    ],
                    'assignedUser' => $expertQuestions[1]->assigned_user_uuid,
                    'type' => $expertQuestions[1]->type->value,
                    'subject' => $expertQuestions[1]->subject,
                    'phone' => $expertQuestions[1]->phone,
                    'question' => $expertQuestions[1]->question,
                ],
                [
                    'uuid' => $expertQuestions[2]->uuid,
                    'caseUuid' => $expertQuestions[2]->case_uuid,
                    'user' => [
                        'name' => $expertQuestions[2]->user->name,
                        'roles' => $expertQuestions[2]->user->getRolesArray(),
                        'uuid' => $expertQuestions[2]->user->uuid,
                    ],
                    'assignedUser' => $expertQuestions[2]->assigned_user_uuid,
                    'type' => $expertQuestions[2]->type->value,
                    'subject' => $expertQuestions[2]->subject,
                    'phone' => $expertQuestions[2]->phone,
                    'question' => $expertQuestions[2]->question,
                ],
            ],
        ], false);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testListExpertQuestionsDoesNotShowUnavailable(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $userRole);
        $this->be($user);

        // GIVEN that there is a second user
        $secondUser = $this->createUser([], $userRole);

        // GIVEN that there are multiple questions for medical supervisor
        $expertQuestions = [
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
        ];

        // GIVEN that there are 2 more which are unavailable because they are already owned
        $this->createExpertQuestionForCase(
            $this->createCaseForOrganisation($organisation),
            ['type' => $expertQuestionType, 'assigned_user_uuid' => $secondUser->uuid],
        );
        $this->createExpertQuestionForCase(
            $this->createCaseForOrganisation($organisation),
            ['type' => $expertQuestionType, 'assigned_user_uuid' => $secondUser->uuid],
        );

        // DO sort the array of expert questions as how they will be set within the response
        usort($expertQuestions, static function ($a, $b) {
            return $a->created_at > $b->created_at;
        });

        // ASSERT that only available question within the response
        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'from' => 1,
            'to' => 2,
            'total' => 2,
            'currentPage' => 1,
            'lastPage' => 1,
            'data' => [
                [
                    'uuid' => $expertQuestions[0]->uuid,
                    'caseUuid' => $expertQuestions[0]->case_uuid,
                    'user' => [
                        'name' => $expertQuestions[0]->user->name,
                        'roles' => $expertQuestions[0]->user->getRolesArray(),
                        'uuid' => $expertQuestions[0]->user->uuid,
                    ],
                    'assignedUser' => $expertQuestions[0]->assigned_user_uuid,
                    'type' => $expertQuestions[0]->type->value,
                    'subject' => $expertQuestions[0]->subject,
                    'phone' => $expertQuestions[0]->phone,
                    'question' => $expertQuestions[0]->question,
                ],
                [
                    'uuid' => $expertQuestions[1]->uuid,
                    'caseUuid' => $expertQuestions[1]->case_uuid,
                    'user' => [
                        'name' => $expertQuestions[1]->user->name,
                        'roles' => $expertQuestions[1]->user->getRolesArray(),
                        'uuid' => $expertQuestions[1]->user->uuid,
                    ],
                    'assignedUser' => $expertQuestions[1]->assigned_user_uuid,
                    'type' => $expertQuestions[1]->type->value,
                    'subject' => $expertQuestions[1]->subject,
                    'phone' => $expertQuestions[1]->phone,
                    'question' => $expertQuestions[1]->question,
                ],
            ],
        ], false);
    }

    public function testListExpertQuestionsDoesShowExpertQuestionThatIsAssignedToUser(): void
    {
        $userRole = 'medical_supervisor';
        $expertQuestionType = ExpertQuestionType::medicalSupervision();

        // GIVEN that we are the user
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $userRole);
        $this->be($user);

        // GIVEN that there is a second user
        $secondUser = $this->createUserForOrganisation($organisation, [], $userRole);

        // GIVEN that there are multiple questions for medical supervisor
        $expertQuestions = [
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
            $this->createExpertQuestionForCase(
                $this->createCaseForOrganisation($organisation),
                ['type' => $expertQuestionType, 'assigned_user_uuid' => $user->uuid],
            ),
        ];

        // GIVEN that there are 2 more which are unavailable because they are already owned
        $this->createExpertQuestionForCase(
            $this->createCaseForOrganisation($organisation),
            ['type' => $expertQuestionType, 'assigned_user_uuid' => $secondUser->uuid],
        );
        $this->createExpertQuestionForCase(
            $this->createCaseForOrganisation($organisation),
            ['type' => $expertQuestionType, 'assigned_user_uuid' => $secondUser->uuid],
        );

        // ASSERT that only available question within the response
        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(count($expertQuestions), 'data');
        for ($i = 0; $i < count($expertQuestions); $i++) {
            $response->assertJsonFragment([
                'uuid' => $expertQuestions[$i]->uuid,
                'caseUuid' => $expertQuestions[$i]->case_uuid,
                'user' => [
                    'name' => $expertQuestions[$i]->user->name,
                    'roles' => $expertQuestions[$i]->user->getRolesArray(),
                    'uuid' => $expertQuestions[$i]->user->uuid,
                ],
                'assignedUser' => $expertQuestions[$i]->assigned_user_uuid ? [
                    'name' => $expertQuestions[$i]->assignedUser->name,
                    'roles' => $expertQuestions[$i]->assignedUser->getRolesArray(),
                    'uuid' => $expertQuestions[$i]->assignedUser->uuid,
                ] : null,
                'type' => $expertQuestions[$i]->type->value,
                'subject' => $expertQuestions[$i]->subject,
                'phone' => $expertQuestions[$i]->phone,
                'question' => $expertQuestions[$i]->question,
            ], false);
        }
    }

    public function testListExpertQuestionsDoesNotShowAnsweredQuestions(): void
    {
        $userRole = 'medical_supervisor';
        $expertQuestionType = ExpertQuestionType::medicalSupervision();

        // GIVEN that we are the user
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $userRole);
        $this->be($user);

        // GIVEN that there is a second user
        $secondUser = $this->createUserForOrganisation($organisation, [], $userRole);

        $this->createExpertQuestionWithAnswerForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]);
        $this->createExpertQuestionWithAnswerForCase(
            $this->createCaseForOrganisation($organisation),
            ['type' => $expertQuestionType, 'assigned_user_uuid' => $secondUser->uuid],
        );

        // ASSERT that only available question within the response
        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(0, 'data');
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testListExpertQuestionsDoesNotShowConversationCoachType(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
        ExpertQuestionType $oppositeExpertQuestionType,
    ): void {
        // GIVEN that we are the user
        $organisation = $this->createOrganisation();
        $this->createCaseForOrganisation($organisation);
        $user = $this->createUserForOrganisation($organisation, [], $userRole);
        $this->be($user);

        // GIVEN that there are multiple questions for medical supervisor
        $expertQuestions = [
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
            $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $expertQuestionType]),
        ];

        // GIVEN that there are 2 more which have the incorrect type
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $oppositeExpertQuestionType]);
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), ['type' => $oppositeExpertQuestionType]);

        // DO sort the array of expert questions as how they will be set within the response
        usort($expertQuestions, static function ($a, $b) {
            return $a->created_at > $b->created_at;
        });

        // ASSERT that only question with the correct type are within the response
        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'from' => 1,
            'to' => 2,
            'total' => 2,
            'currentPage' => 1,
            'lastPage' => 1,
            'data' => [
                [
                    'uuid' => $expertQuestions[0]->uuid,
                    'caseUuid' => $expertQuestions[0]->case_uuid,
                    'user' => [
                        'name' => $expertQuestions[0]->user->name,
                        'roles' => $expertQuestions[0]->user->getRolesArray(),
                        'uuid' => $expertQuestions[0]->user->uuid,
                    ],
                    'assignedUser' => $expertQuestions[0]->assigned_user_uuid,
                    'type' => $expertQuestions[0]->type->value,
                    'subject' => $expertQuestions[0]->subject,
                    'phone' => $expertQuestions[0]->phone,
                    'question' => $expertQuestions[0]->question,
                ],
                [
                    'uuid' => $expertQuestions[1]->uuid,
                    'caseUuid' => $expertQuestions[1]->case_uuid,
                    'user' => [
                        'name' => $expertQuestions[1]->user->name,
                        'roles' => $expertQuestions[1]->user->getRolesArray(),
                        'uuid' => $expertQuestions[1]->user->uuid,
                    ],
                    'assignedUser' => $expertQuestions[1]->assigned_user_uuid,
                    'type' => $expertQuestions[1]->type->value,
                    'subject' => $expertQuestions[1]->subject,
                    'phone' => $expertQuestions[1]->phone,
                    'question' => $expertQuestions[1]->question,
                ],
            ],
        ], false);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testListExpertQuestionDoesNotShowOutdatedCases(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
        ExpertQuestionType $oppositeExpertQuestionType,
    ): void {
        // GIVEN that we are the user
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $userRole);
        $this->be($user);

        // Given that there is a created_at date that over the config setting
        $createdAtDate = CarbonImmutable::now()->subDays((int) config('misc.supervision.questions_recent_days') + 1);

        // GIVEN that there are multiple questions for medical supervisor which are outdated
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), [
            'type' => $expertQuestionType,
            'created_at' => $createdAtDate,
        ]);
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), [
            'type' => $expertQuestionType,
            'created_at' => $createdAtDate,
        ]);

        // GIVEN that there is one question which is not outdated
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($organisation), [
            'type' => $expertQuestionType,
            'created_at' => CarbonImmutable::now(),
        ]);

        // ASSERT that only one question is shown
        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        self::assertEquals(1, $response->json('total'));
        self::assertCount(1, $response->json('data'));
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testListExpertQuestionDoesNotShowQuestionsFromOtherOrganisation(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $currentOrganisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($currentOrganisation, [], $userRole);
        $this->be($user);

        $otherOrganisation = $this->createOrganisation();

        // GIVEN that there are multiple questions for medical supervisor which are outdated
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($currentOrganisation), ['type' => $expertQuestionType]);
        $this->createExpertQuestionForCase($this->createCaseForOrganisation($otherOrganisation), ['type' => $expertQuestionType]);

        // ASSERT that only one question is shown
        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        self::assertEquals(1, $response->json('total'));
        self::assertCount(1, $response->json('data'));
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testListExpertQuestionCanNotAccessListFromOtherType(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
        ExpertQuestionType $oppositeExpertQuestionType,
    ): void {
        // GIVEN that we are the user
        $currentOrganisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($currentOrganisation, [], $userRole);
        $this->be($user);

        // ASSERT that only one question is shown
        $response = $this->get("/api/expert-questions?type={$oppositeExpertQuestionType}");
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testListExpertQuestionOnlyFromUsersWithinYourOrganisationForCaseThatIsOwnedByYourOrganisation(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $currentOrganisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($currentOrganisation, [], $userRole);
        $this->be($user);

        $ownOrganisationUser = $this->createUserForOrganisation($currentOrganisation);
        $otherOrganisation = $this->createOrganisation();
        $otherOrganisationUser = $this->createUserForOrganisation($otherOrganisation);
        $case = $this->createCaseForOrganisation($currentOrganisation);

        $this->createExpertQuestionForCase($case, ['user_uuid' => $ownOrganisationUser->uuid, 'type' => $expertQuestionType]);
        $this->createExpertQuestionForCase($case, ['user_uuid' => $otherOrganisationUser->uuid, 'type' => $expertQuestionType]);

        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(1, 'data');
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testListExpertQuestionOnlyFromUsersWithinYourOrganisationForCaseThatIsOwnedByOtherOrganisation(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $currentOrganisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($currentOrganisation, [], $userRole);
        $this->be($user);

        $ownOrganisationUser = $this->createUserForOrganisation($currentOrganisation);
        $otherOrganisation = $this->createOrganisation();
        $otherOrganisationUser = $this->createUserForOrganisation($otherOrganisation);
        $case = $this->createCaseForOrganisation($otherOrganisation);

        $this->createExpertQuestionForCase($case, ['user_uuid' => $ownOrganisationUser->uuid, 'type' => $expertQuestionType]);
        $this->createExpertQuestionForCase($case, ['user_uuid' => $otherOrganisationUser->uuid, 'type' => $expertQuestionType]);

        $response = $this->get("/api/expert-questions?type={$expertQuestionType}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(1, 'data');
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testGetExpertQuestion(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
        ]);

        // ASSERT that the get route for the expert question gives back the correct data
        $this->get("/api/expert-questions/{$expertQuestion->uuid}", [])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'uuid' => $expertQuestion->uuid,
                'caseUuid' => $expertQuestion->case_uuid,
                'user' => [
                    'name' => $expertQuestion->user->name,
                    'roles' => $expertQuestion->user->getRolesArray(),
                    'uuid' => $expertQuestion->user->uuid,
                ],
                'assignedUser' => $expertQuestion->assigned_user_uuid,
                'type' => $expertQuestion->type->value,
                'subject' => $expertQuestion->subject,
                'phone' => $expertQuestion->phone,
                'question' => $expertQuestion->question,
                'answer' => null,
            ], false);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testGetExpertQuestionWithIncorrectTypeIsNotFound(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
        ExpertQuestionType $oppositeExpertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question with the wrong type
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $oppositeExpertQuestionType,
        ]);

        // ASSERT that the get route for the medical supervisor expert question fails
        //      & returns a not found status
        $this->get("/api/expert-questions/{$expertQuestion->uuid}", [])
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testGetExpertQuestionThatIsAlreadyPickedUpIsUnavailable(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that there is a second user
        $secondUser = $this->createUser([], $userRole);

        // GIVEN that we are given an expert question which the second user has picked up
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
            'assigned_user_uuid' => $secondUser->uuid,
        ]);

        // ASSERT that the get route for the medical supervisor expert question fails
        //      & returns a gone entity status
        $this->get("/api/expert-questions/{$expertQuestion->uuid}", [])
            ->assertStatus(Response::HTTP_GONE);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testExpertQuestionCanBeAssignedToExpertUserUuid(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question with the wrong type
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
        ]);

        // DO action on route & ASSERT that the response is OK
        $this->post("/api/expert-questions/{$expertQuestion->uuid}/assignment", [
            'assigned_user_uuid' => $user->uuid,
        ])->assertStatus(Response::HTTP_OK);

        // ASSERT database has correct data
        $this->assertDatabaseHas('expert_question', [
            'uuid' => $expertQuestion->uuid,
            'assigned_user_uuid' => $user->uuid,
        ]);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testExpertQuestionCanBeAnswered(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question with the wrong type
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
        ]);

        // GIVEN an answer
        $answer = $this->faker->paragraph();

        // DO action on route & ASSERT that the response is OK
        $this->put("/api/expert-questions/{$expertQuestion->uuid}/answer", [
            'answer' => $answer,
        ])->assertStatus(Response::HTTP_OK);

        // ASSERT database has correct data
        $this->assertDatabaseCount('expert_question_answer', 1);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testExpertQuestionAssignedUserUuidIsNullAfterAnswer(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
        ]);

        // GIVEN an answer
        $answer = $this->faker->paragraph();

        // DO action on route & ASSERT that the response is OK
        $this->put("/api/expert-questions/{$expertQuestion->uuid}/answer", [
            'answer' => $answer,
        ])->assertStatus(Response::HTTP_OK);

        // Make sure database has only one record
        $this->assertDatabaseCount('expert_question', 1);

        // ASSERT database has the assigned user uuid unassigned
        $this->assertDatabaseHas('expert_question', [
            'assigned_user_uuid' => null,
        ]);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testExpertQuestionCanNotBeAnsweredIfAnswerAlreadyExists(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question with the wrong type
        $expertQuestion = $this->createExpertQuestionWithAnswerForCase($this->createCase(), [
            'type' => $expertQuestionType,
        ]);

        // GIVEN an answer
        $answer = $this->faker->paragraph();

        // DO action on route & ASSERT that the response is unprocessable
        $this->putJson("/api/expert-questions/{$expertQuestion->uuid}/answer", [
            'answer' => $answer,
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testExpertQuestionCanNotBeAnsweredIfIncorrectType(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
        ExpertQuestionType $oppositeExpertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question with the wrong type
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $oppositeExpertQuestionType,
        ]);

        // GIVEN an answer
        $answer = $this->faker->paragraph();

        // DO action on route & ASSERT that the response is not found
        $this->putJson("/api/expert-questions/{$expertQuestion->uuid}/answer", [
            'answer' => $answer,
        ])->assertStatus(Response::HTTP_NOT_FOUND);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testExpertQuestionCanNotBeAnsweredIfAlreadyPickedUpByOtherUser(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        $secondUser = $this->createUser([], $userRole);

        // GIVEN that we are given an expert question with the wrong type
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
            'assigned_user_uuid' => $secondUser->uuid,
        ]);

        // GIVEN an answer
        $answer = $this->faker->paragraph();

        // DO action on route & ASSERT that the response is gone
        $this->put("/api/expert-questions/{$expertQuestion->uuid}/answer", [
            'answer' => $answer,
        ])->assertStatus(Response::HTTP_GONE);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testExpertQuestionAnswerCanNotExceedMaximalLength(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question assigned to the user
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
            'assigned_user_uuid' => $user->uuid,
        ]);

        // GIVEN an answer that is longer then 5000 characters
        $answer = $this->faker->text(6000);

        // DO action on route & ASSERT that the response is found but validation error (unprocessable entity)
        $this->putJson("/api/expert-questions/{$expertQuestion->uuid}/answer", [
            'answer' => $answer,
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // GIVEN an answer that is shorter than 5000 characters
        $answer = $this->faker->text(4000);

        // DO action on route & ASSERT that the response is OK
        $this->putJson("/api/expert-questions/{$expertQuestion->uuid}/answer", [
            'answer' => $answer,
        ])->assertStatus(Response::HTTP_OK);
    }

// phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
//    @TODO: criteria right now is that the question will not be shown if it has been answered, this might change in the future which given me the reason to keep this test
    //    #[DataProvider('expertQuestionRolesDataProvider')]
    //    public function testGetExpertQuestionIncludesAnswerIfAnswered(
    //        string $userRole,
    //        ExpertQuestionType $expertQuestionType
    //    ): void {
    //        // GIVEN that we are the user
    //        $user = $this->createUser([], $userRole);
    //        $this->be($user);
    //
    //        // GIVEN a known answer
    //        $answer = $this->faker->paragraph;
    //
    //        // GIVEN that we are given an expert question
    //        $expertQuestion = $this->createExpertQuestionWithAnswerForCase($this->createCase(), [
    //            'type' => $expertQuestionType,
    //        ], [
    //            'answer' => $answer,
    //            'answered_by' => $user->uuid,
    //        ]);
    //
    //        // ASSERT that the get route for the expert question gives back the correct data
    //        $this->get("/api/expert-questions/{$expertQuestion->uuid}", [])
    //            ->assertStatus(Response::HTTP_OK)
    //            ->assertJson(['answer' => ['value' => $answer]], false);
    //    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testDoNotGetExpertQuestionThatAlreadyHasBeenAnswered(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN a known answer
        $answer = $this->faker->paragraph;

        // GIVEN that we are given an expert question
        $expertQuestion = $this->createExpertQuestionWithAnswerForCase($this->createCase(), [
            'type' => $expertQuestionType,
        ], [
            'answer' => $answer,
        ]);

        // ASSERT that the get route for the expert question gives back the correct data
        $this->getJson("/api/expert-questions/{$expertQuestion->uuid}", [])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testGetExpertQuestionIncludesAnswerButIsNullIfNotAnswered(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        $user = $this->createUser(roles: $userRole);
        $this->be($user);
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
        ]);

        $this->get(sprintf('/api/expert-questions/%s', $expertQuestion->uuid))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'answer' => null,
            ]);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testCreatedExpertQuestionAnswerIsEncrypted(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that we are given an expert question assigned to the user
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
            'assigned_user_uuid' => $user->uuid,
        ]);

        // DO action on route & ASSERT that the response is OK
        $this->putJson("/api/expert-questions/{$expertQuestion->uuid}/answer", [
            'answer' => str::random(200),
        ])->assertStatus(Response::HTTP_OK);

        $expertQuestionAnswer = DB::table('expert_question_answer')->first();
        $this->assertStringContainsString('ciphertext', $expertQuestionAnswer->answer);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testExpertQuestionCanNotBeAssignedToOtherUser(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that there is a second user
        $secondUser = $this->createUser([], $userRole);

        // GIVEN that we are given an expert question with the wrong type
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
        ]);

        // DO action on route & ASSERT that the response is unprocessable
        $this->post("/api/expert-questions/{$expertQuestion->uuid}/assignment", [
            'assigned_user_uuid' => $secondUser->uuid,
        ])->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        // ASSERT database has not changed & is still not assigned
        $this->assertDatabaseHas('expert_question', [
            'uuid' => $expertQuestion->uuid,
            'assigned_user_uuid' => null,
        ]);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testCanUnassignExpertQuestionIfAssignedToSelf(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN we have a question set to our name
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
            'assigned_user_uuid' => $user->uuid,
        ]);

        // ASSERT that the route return a 200 status
        $this->delete("/api/expert-questions/{$expertQuestion->uuid}/assignment", [])
            ->assertStatus(Response::HTTP_OK);

        // ASSERT that expert question is not unassigned
        $this->assertDatabaseHas('expert_question', [
            'uuid' => $expertQuestion->uuid,
            'assigned_user_uuid' => null,
        ]);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testCanNotUnassignExpertQuestionIfNotAssignedToSelf(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN that there is a second user
        $secondUser = $this->createUser([], $userRole);

        // GIVEN that the question is assigned to the second user
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
            'assigned_user_uuid' => $secondUser->uuid,
        ]);

        // ASSERT that the route is gone (Default if question is not available)
        $this->delete("/api/expert-questions/{$expertQuestion->uuid}/assignment", [])
            ->assertStatus(Response::HTTP_GONE);

        // ASSERT the question is still assigned to the second user
        $this->assertDatabaseHas('expert_question', [
            'uuid' => $expertQuestion->uuid,
            'assigned_user_uuid' => $secondUser->uuid,
        ]);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testCanNotUnassignExpertQuestionIfNotAssigned(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        // GIVEN that we are the user
        $user = $this->createUser([], $userRole);
        $this->be($user);

        // GIVEN we have a question set to our name
        $expertQuestion = $this->createExpertQuestionForCase($this->createCase(), [
            'type' => $expertQuestionType,
            'assigned_user_uuid' => null,
        ]);

        // ASSERT that the route return an unproccessable entity
        $this->delete("/api/expert-questions/{$expertQuestion->uuid}/assignment", [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // ASSERT that expert question is still not unassigned
        $this->assertDatabaseHas('expert_question', [
            'uuid' => $expertQuestion->uuid,
            'assigned_user_uuid' => null,
        ]);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testFindExpertQuestionByCaseId(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        $organisation = $this->createOrganisation();
        $this->givenALoggedSupervisorWithRoleFromOrganisation($userRole, $organisation);
        $covidCase = $this->givenACaseWithIdForOrganisation('7777777', $organisation);
        $this->givenAnExpertQuestionWithSubjectAndTypeForACase('What is love?', $expertQuestionType, $covidCase);
        $response = $this->whenINavigateToAnExpertQuestionByCaseId('7777777', $expertQuestionType);
        $this->thenIShouldSeeAnExpertQuestionWithSubject('What is love?', $response);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testFindExpertQuestionByCaseIdBadRequest(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        $this->givenALoggedInUserWithRole($userRole);
        $response = $this->whenITryToNavigateToAnExpertQuestionWithoutCaseId($expertQuestionType);
        $this->thenTheResponseShouldBeUnprocessable($response);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testFindExpertQuestionByCaseIdWithIncorrectExpertQuestionType(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
        ExpertQuestionType $oppositeExpertQuestionType,
    ): void {
        $organisation = $this->createOrganisation();
        $this->givenALoggedSupervisorWithRoleFromOrganisation($userRole, $organisation);
        $covidCase = $this->givenACaseWithIdForOrganisation('7777777', $organisation);
        $this->givenAnExpertQuestionWithSubjectAndTypeForACase('What is love?', $oppositeExpertQuestionType, $covidCase);
        $response = $this->whenINavigateToAnExpertQuestionByCaseId('7777777', $expertQuestionType);
        $this->thenTheExpertQuestionShouldNotBeFound($response);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testFindExpertQuestionByNonExistingCaseId(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        $organisation = $this->createOrganisation();
        $this->givenALoggedSupervisorWithRoleFromOrganisation($userRole, $organisation);
        $response = $this->whenINavigateToAnExpertQuestionByCaseId('0000000', $expertQuestionType);
        $this->thenTheExpertQuestionShouldNotBeFound($response);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testFindExpertQuestionByCaseIdFromOtherOrganisation(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        $organisation = $this->createOrganisation();
        $otherOrganisation = $this->createOrganisation();

        $this->givenALoggedInUserWithRole($userRole, $organisation);
        $covidCase = $this->givenACaseWithIdForOrganisation('7777777', $otherOrganisation);
        $this->givenAnExpertQuestionWithSubjectAndTypeForACase('What is love?', $expertQuestionType, $covidCase);
        $response = $this->whenINavigateToAnExpertQuestionByCaseId('7777777', $expertQuestionType);
        $this->thenTheExpertQuestionShouldNotBeFound($response);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testFindExpertQuestionByCaseIdPickedUpByOtherSupervisor(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        $organisation = $this->createOrganisation();
        $user = $this->givenALoggedSupervisorWithRoleFromOrganisation($userRole, $organisation);
        $covidCase = $this->givenACaseWithIdForOrganisation('7777777', $organisation);
        $expertQuestion = $this->givenAnExpertQuestionWithSubjectAndTypeForACase('What is love?', $expertQuestionType, $covidCase);
        $this->givenTheExpertQuestionIsAssignedToTheUser($expertQuestion, $user);
        $this->givenALoggedSupervisorWithRoleFromOrganisation($userRole, $organisation);
        $response = $this->whenINavigateToAnExpertQuestionByCaseId('7777777', $expertQuestionType);
        $this->thenTheExpertQuestionShouldBeConflicted($response);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testFindExpertQuestionByCaseIdOldestUnanswered(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        $organisation = $this->createOrganisation();

        $this->givenALoggedSupervisorWithRoleFromOrganisation($userRole, $organisation);
        $covidCase = $this->givenACaseWithIdForOrganisation('7777777', $organisation);

        $questions = [
            [4, '-', true],
            [3, 'The oldest unanswered question', false],
            [3, '-', true],
            [2, '-', false],
            [2, '-', true],
            [1, '-', false],
            [1, '-', true],
        ];

        foreach ($questions as $question) {
            $this->givenAnExpertQuestionWithSubjectAndTypeAndAnswerForACaseDaysAgo(
                $question[0],
                $question[1],
                $expertQuestionType,
                $covidCase,
                $question[2],
            );
        }

        $response = $this->whenINavigateToAnExpertQuestionByCaseId('7777777', $expertQuestionType);
        $this->thenIShouldSeeAnExpertQuestionWithSubject('The oldest unanswered question', $response);
    }

    #[DataProvider('expertQuestionRolesDataProvider')]
    public function testFindExpertQuestionByCaseIdNewestAnsweredIfNoUnansweredExists(
        string $userRole,
        ExpertQuestionType $expertQuestionType,
    ): void {
        $organisation = $this->createOrganisation();

        $this->givenALoggedSupervisorWithRoleFromOrganisation($userRole, $organisation);
        $covidCase = $this->givenACaseWithIdForOrganisation('7777777', $organisation);

        $questions = [
            [4, '-', true],
            [3, '-', true],
            [2, '-', true],
            [1, 'The newest answered question', true],
        ];

        foreach ($questions as $question) {
            $this->givenAnExpertQuestionWithSubjectAndTypeAndAnswerForACaseDaysAgo(
                $question[0],
                $question[1],
                $expertQuestionType,
                $covidCase,
                $question[2],
            );
        }

        $response = $this->whenINavigateToAnExpertQuestionByCaseId('7777777', $expertQuestionType);
        $this->thenIShouldSeeAnExpertQuestionWithSubject('The newest answered question', $response);
    }

    private function givenALoggedInUserWithRole(string $userRole): EloquentUser
    {
        $user = $this->createUser([], $userRole);
        $this->be($user);

        return $user;
    }

    private function givenALoggedSupervisorWithRoleFromOrganisation(
        string $userRole,
        EloquentOrganisation $organisation,
    ): EloquentUser {
        $user = $this->createUserForOrganisation($organisation, [], $userRole);
        $this->be($user);

        return $user;
    }

    private function givenACaseWithIdForOrganisation(string $caseId, EloquentOrganisation $organisation): EloquentCase
    {
        return $this->createCaseForOrganisation($organisation, ['case_id' => $caseId]);
    }

    private function givenAnExpertQuestionWithSubjectAndTypeForACase(
        string $subject,
        ExpertQuestionType $type,
        EloquentCase $covidCase,
    ): ExpertQuestion {
        return $this->createExpertQuestionForCase($covidCase, [
            'subject' => $subject,
            'type' => $type,
        ]);
    }

    private function givenAnExpertQuestionWithSubjectAndTypeAndAnswerForACaseDaysAgo(
        int $daysAgo,
        string $subject,
        ExpertQuestionType $type,
        EloquentCase $covidCase,
        bool $addAnswer,
    ): ExpertQuestion {
        $questionAttributes = [
            'subject' => $subject,
            'created_at' => CarbonImmutable::now()->subDays($daysAgo),
            'type' => $type,
        ];
        if ($addAnswer) {
            return $this->createExpertQuestionWithAnswerForCase($covidCase, $questionAttributes, ['answer' => 'go on...']);
        }
        return $this->createExpertQuestionForCase($covidCase, $questionAttributes);
    }

    private function givenTheExpertQuestionIsAssignedToTheUser(ExpertQuestion $expertQuestion, EloquentUser $user): void
    {
        $this->post("/api/expert-questions/{$expertQuestion->uuid}/assignment", [
            'assigned_user_uuid' => $user->uuid,
        ]);
    }

    private function whenINavigateToAnExpertQuestionByCaseId(
        string $caseId,
        ExpertQuestionType $expertQuestionType,
    ): TestResponse {
        return $this->postJson(self::PATH_FIND_BY_CASE_ID, [
            FindByCaseIdRequest::FIELD_CASE_ID => $caseId,
            FindByCaseIdRequest::FIELD_EXPERT_QUESTION_TYPE => $expertQuestionType,
        ]);
    }

    private function thenIShouldSeeAnExpertQuestionWithSubject(string $subject, TestResponse $response): void
    {
        $response->assertOk()
            ->assertJson(['subject' => $subject]);
    }

    private function whenITryToNavigateToAnExpertQuestionWithoutCaseId(ExpertQuestionType $expertQuestionType): TestResponse
    {
        return $this->postJson(self::PATH_FIND_BY_CASE_ID, [
            FindByCaseIdRequest::FIELD_EXPERT_QUESTION_TYPE => $expertQuestionType,
        ]);
    }

    private function thenTheResponseShouldBeUnprocessable(TestResponse $response): void
    {
        $response->assertUnprocessable();
    }

    private function thenTheExpertQuestionShouldNotBeFound(TestResponse $response): void
    {
        $response->assertNotFound();
    }

    private function thenTheExpertQuestionShouldBeConflicted(TestResponse $response): void
    {
        $response->assertStatus(Response::HTTP_CONFLICT);
    }
}
