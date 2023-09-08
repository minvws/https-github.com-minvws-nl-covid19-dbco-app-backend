<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Services\Timeline\TimelineService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;
use function array_keys;
use function sprintf;

#[Group('planner-case')]
class ApiPlannerCaseTimelineControllerTest extends FeatureTestCase
{
    public function testTimelineNotes(): void
    {
        $user = $this->createUser([], 'planner,user');
        $case = $this->createCaseForUser($user);
        $dbNote = $this->createNoteForCase($case);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid));

        $responseNote = $response->json()[0];
        $this->assertEquals([
            "uuid",
            "note",
            "title",
            "time",
            "timelineable_id",
            "timelineable_type",
        ], array_keys($responseNote));

        $this->assertSame($dbNote->uuid, $responseNote['timelineable_id']);
        $this->assertSame('note', $responseNote['timelineable_type']);
        $this->assertSame($dbNote->note, $responseNote['note']);
    }

    public function testTimelineAssignment(): void
    {
        $user = $this->createUser([], 'planner,user');
        $case = $this->createCaseForUser($user);
        $this->createAssignmentHistoryForCase($case, [
            'assigned_at' => CarbonImmutable::create(2021, 7, 16)->modify('-1 day'),
        ]);
        $this->createAssignmentHistoryForCase($case, ['assigned_at' => CarbonImmutable::create(2021, 7, 16)]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid));

        $responseHistory1 = $response->json()[0];
        $responseHistory2 = $response->json()[1];

        // Relative time is appended to tile
        $this->assertSame('16 juli 2021  om  02:00 • Gedurende 1 dag', $responseHistory1['time']);
        $this->assertSame('15 juli 2021  om  02:00', $responseHistory2['time']);
    }

    public function testTimelineExpertQuestion(): void
    {
        $user = $this->createUser([], 'planner,user');
        $case = $this->createCaseForUser($user);
        $dbExpertQuestion = $this->createExpertQuestionForCase($case, [
            'created_at' => CarbonImmutable::create(2022, 3, 17, 15, 00, 00),
            'type' => ExpertQuestionType::medicalSupervision(),
            'user_uuid' => $user->uuid,
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid));

        $responseExpertQuestion = $response->json()[0];

        $this->assertEquals([
            'uuid',
            'note',
            'title',
            'author_user',
            'time',
            'timelineable_id',
            'timelineable_type',
            'answer',
            'answer_user',
            'answer_time',
        ], array_keys($responseExpertQuestion));

        $this->assertSame($dbExpertQuestion->timeline->uuid, $responseExpertQuestion['uuid']);
        $this->assertSame($dbExpertQuestion->question, $responseExpertQuestion['note']);
        $this->assertSame($dbExpertQuestion->subject, $responseExpertQuestion['title']);
        $this->assertSame($user->name, $responseExpertQuestion['author_user']);
        $this->assertSame('17 maart 2022  om  16:00 • Hulpvraag aan Medische Supervisie', $responseExpertQuestion['time']);
        $this->assertSame($dbExpertQuestion->uuid, $responseExpertQuestion['timelineable_id']);
        $this->assertSame('expert-question', $responseExpertQuestion['timelineable_type']);
    }

    public function testTimelineExpertQuestionWithAnswer(): void
    {
        $user = $this->createUser([], 'planner,user');
        $case = $this->createCaseForUser($user);
        $dbExpertQuestionWithAnswer = $this->createExpertQuestionWithAnswerForCase(
            $case,
            [
                'created_at' => CarbonImmutable::create(2022, 4, 5, 14, 00, 00),
                'type' => ExpertQuestionType::medicalSupervision(),
                'user_uuid' => $user->uuid,
            ],
            [
                'created_at' => CarbonImmutable::create(2022, 4, 5, 14, 00, 00),
                'answered_by' => $this->createUser(['name' => 'Henk Testmeneer'], 'medical_supervisor'),
            ],
        );

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid));

        $responseExpertQuestion = $response->json()[0];

        $this->assertEquals([
            "uuid",
            "note",
            "title",
            "author_user",
            "time",
            "timelineable_id",
            "timelineable_type",
            "answer",
            "answer_user",
            "answer_time",
        ], array_keys($responseExpertQuestion));

        $this->assertSame($dbExpertQuestionWithAnswer->timeline->uuid, $responseExpertQuestion['uuid']);
        $this->assertSame($dbExpertQuestionWithAnswer->question, $responseExpertQuestion['note']);
        $this->assertSame($dbExpertQuestionWithAnswer->subject, $responseExpertQuestion['title']);
        $this->assertSame($user->name, $responseExpertQuestion['author_user']);
        $this->assertSame('5 april 2022  om  16:00 • Hulpvraag aan Medische Supervisie', $responseExpertQuestion['time']);
        $this->assertSame($dbExpertQuestionWithAnswer->uuid, $responseExpertQuestion['timelineable_id']);
        $this->assertSame('expert-question', $responseExpertQuestion['timelineable_type']);
        $this->assertSame($dbExpertQuestionWithAnswer->answer->answer, $responseExpertQuestion['answer']);
        $this->assertSame('Henk Testmeneer, Medisch supervisor', $responseExpertQuestion['answer_user']);
        $this->assertSame('5 april 2022  om  16:00', $responseExpertQuestion['answer_time']);
    }

    public function testTimelineUserPermissionFilter(): void
    {
        $organisation = $this->createOrganisation();

        $bcoUser = $this->createUserForOrganisation($organisation, [], 'user');
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        $case = $this->createCaseForUser($bcoUser);

        $this->createExpertQuestionWithAnswerForCase(
            $case,
            [
                'created_at' => CarbonImmutable::create(2022, 4, 5, 14, 00, 00),
                'type' => ExpertQuestionType::medicalSupervision(),
                'user_uuid' => $bcoUser->uuid,
            ],
            [
                'created_at' => CarbonImmutable::create(2022, 4, 5, 14, 00, 00),
                'answered_by' => $this->createUser(['name' => 'Henk Testmeneer'], 'medical_supervisor'),
            ],
        );

        $this->createNoteForCase($case);
        $this->createAssignmentHistoryForCase($case, ['assigned_at' => CarbonImmutable::create(2021, 7, 16)->modify('-1 day')]);

        $response = $this->be($bcoUser)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid));
        $this->assertStringContainsString('Testmeneer', $response->content());
        $this->assertCount(3, $response->json());

        $plannerResponse = $this->be($planner)->getJson(sprintf('/api/cases/%s/planner-timeline', $case->uuid));
        $this->assertStringNotContainsString('Testmeneer', $plannerResponse->content());
        $this->assertCount(2, $plannerResponse->json());

        $this->assertEquals(403, $this->be($planner)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid))->getStatusCode());
        $this->assertEquals(403, $this->be($bcoUser)->getJson(sprintf('/api/cases/%s/planner-timeline', $case->uuid))->getStatusCode());
    }

    public function testTimelineCallToAction(): void
    {
        /** @var TimelineService $timelineService */
        $timelineService = app(TimelineService::class);

        $user = $this->createUser([], 'planner,user');
        $case = $this->createCaseForUser($user);

        $callToAction = $this->createCallToAction([
            'created_by' => $user->uuid,
        ]);
        $callToActionResource = $this->createResourceForCallToAction($callToAction);
        $this->createChoreForCaseAndOrganisation($case, $user->getOrganisation(), [
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
        ]);

        $timelineService->addToTimeline($callToAction);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid));

        $responseCallToAction = $response->json()[0];

        $this->assertEquals([
            "uuid",
            "note",
            "title",
            "author_user",
            "time",
            "timelineable_id",
            "timelineable_type",
            "call_to_action_uuid",
            "call_to_action_deadline",
        ], array_keys($responseCallToAction));

        $this->assertSame($callToAction->timeline->uuid, $responseCallToAction['uuid']);
        $this->assertSame($callToAction->description, $responseCallToAction['note']);
        $this->assertSame($callToAction->subject, $responseCallToAction['title']);
        $this->assertSame($user->name, $responseCallToAction['author_user']);
        $this->assertSame($callToAction->uuid, $responseCallToAction['timelineable_id']);
        $this->assertSame($callToAction->uuid, $responseCallToAction['call_to_action_uuid']);
        $this->assertSame('call-to-action', $responseCallToAction['timelineable_type']);
    }

    #[DataProvider('plannerTimelineIsAccessibleByRolesDataProvider')]
    public function testPlannerTimelineIsAccessibleByRoles(string $plannerRole, int $statusCode): void
    {
        $organisation = $this->createOrganisation();
        $bcoUser = $this->createUserForOrganisation($organisation);
        $planner = $this->createUserForOrganisation($organisation, [], $plannerRole);
        $case = $this->createCaseForUser($bcoUser);
        $this->createNoteForCase($case);

        $this->assertEquals(
            $statusCode,
            $this->be($planner)->getJson(sprintf('/api/cases/%s/planner-timeline', $case->uuid))->getStatusCode(),
        );
    }

    public static function plannerTimelineIsAccessibleByRolesDataProvider(): array
    {
        return [
            ['planner', 200],
            ['planner_nationwide', 200],
            ['planner,user', 200],
            ['user', 403],
            ['casequality', 403],
        ];
    }

    #[DataProvider('userTimelineIsAccessibleByRolesDataProvider')]
    public function testUserTimelineIsAccessibleByRoles(
        string $userRole,
        int $expectedStatus,
    ): void {
        $user = $this->createUser([], $userRole);
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid));
        $this->assertStatus($response, $expectedStatus);
    }

    public static function userTimelineIsAccessibleByRolesDataProvider(): array
    {
        return [
            'planner regional' => ['planner', 403],
            'planner nationwide' => ['planner_nationwide', 403],
            'planner & user' => ['planner,user', 200],
            'user' => ['user', 200],
            'user_nationwide' => ['user_nationwide', 200],
            'dossiercheker' => ['casequality', 200],
            'dossiercherker landelijk' => ['casequality_nationwide', 200],
            'compliance' => ['compliance', 403],
            'contextmanager' => ['contextmanager', 403],
            'clusterspecialist' => ['clusterspecialist', 200],
        ];
    }

    public function testRandomOrganisationDoesNotHaveAccessToTimeline(): void
    {
        $organisation = $this->createOrganisation();
        $bcoUser = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($bcoUser);
        $this->createNoteForCase($case);

        $this->assertEquals(
            404,
            $this->be($this->createUser([], 'planner'))->getJson(sprintf('/api/cases/%s/planner-timeline', $case->uuid))->getStatusCode(),
        );
    }
}
