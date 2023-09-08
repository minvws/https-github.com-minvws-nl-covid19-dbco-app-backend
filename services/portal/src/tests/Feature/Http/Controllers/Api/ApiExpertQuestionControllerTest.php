<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\OrganisationType;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('supervision')]
final class ApiExpertQuestionControllerTest extends FeatureTestCase
{
    public function testCreateExpertQuestion(): void
    {
        $user = $this->createUser([], 'user', ['name' => 'GGD West-Brabant']);
        $case = $this->createCaseForUser($user);

        $payload = [
            'type' => ExpertQuestionType::medicalSupervision()->value,
            'subject' => 'Is this my question?',
            'phone' => '0612312312',
            'question' => 'Is this how this wôrks?',
        ];

        $this->assertDatabaseMissing('expert_question', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $response = $this->be($user)->postJson(sprintf('/api/case/%s/expertQuestion', $case->uuid), $payload);

        $response->assertStatus(201);
        $json = $response->json();

        $this->assertTrue(isset($json['uuid']));
        $this->assertIsString($json['uuid']);
        $this->assertSame($user->uuid, $json['user']['uuid']);
        $this->assertSame($case->uuid, $json['caseUuid']);
        $this->assertSame('medical-supervision', $json['type']);
        $this->assertSame('Is this my question?', $json['subject']);
        $this->assertSame('Is this how this wôrks?', $json['question']);
        $this->assertSame('GGD West-Brabant', $json['caseOrganisationName']);
        $this->assertNotContains('caseCreatedAt', $json);

        $this->assertDatabaseHas('expert_question', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    public function testUpdateExpertQuestion(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $expertQuestion = $this->createExpertQuestionForCase(
            $case,
            ['subject' => 'My Question', 'question' => 'Vraag?', 'user_uuid' => $user->uuid],
        );

        $payload = [
            'uuid' => $expertQuestion->uuid,
            'type' => ExpertQuestionType::medicalSupervision()->value,
            'phone' => null,
            'subject' => 'noitseuQ yM',
            'question' => '?gaarV',
        ];

        $response = $this->be($user)->putJson(sprintf('/api/case/%s/expertQuestion/%s', $case->uuid, $expertQuestion->uuid), $payload);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertSame('medical-supervision', $json['type']);
        $this->assertSame('noitseuQ yM', $json['subject']);
        $this->assertSame('?gaarV', $json['question']);
    }

    #[DataProvider('accessDataProvider')]
    public function testPermissionToCreateQuestion(string $currentUserUuid, bool $allowed): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, ['uuid' => 'user']);
        $case = $this->createCaseForUser($user);

        $this->createUserForOrganisation($organisation, ['uuid' => 'other_user']);

        $payload = [
            'type' => ExpertQuestionType::medicalSupervision()->value,
            'subject' => 'Is this my question?',
            'question' => 'Is this how this wôrks?',
        ];

        $response = $this->be(EloquentUser::find($currentUserUuid))->postJson(
            sprintf('/api/case/%s/expertQuestion', $case->uuid),
            $payload,
        );

        $response->assertStatus($allowed ? 201 : 403);
    }

    #[DataProvider('accessDataProvider')]
    public function testPermissionToUpdateQuestion(string $currentUserUuid, bool $allowed): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, ['uuid' => 'user']);
        $case = $this->createCaseForUser($user);
        $expertQuestion = $this->createExpertQuestionForCase($case, ['user_uuid' => $user->uuid]);

        $this->createUserForOrganisation($organisation, ['uuid' => 'other_user']);

        $payload = [
            'uuid' => $expertQuestion->uuid,
            'subject' => $expertQuestion->subject,
            'type' => $expertQuestion->type->value,
            'phone' => null,
            'question' => 'Is this how this wôrks?',
        ];

        $response = $this->be(EloquentUser::find($currentUserUuid))->putJson(
            sprintf('/api/case/%s/expertQuestion/%s', $case->uuid, $expertQuestion->uuid),
            $payload,
        );

        $response->assertStatus($allowed ? 200 : 403);
    }

    public static function accessDataProvider(): array
    {
        return [
            ['user', true],
            ['other_user', false],
        ];
    }

    #[DataProvider('phoneTestProvider')]
    public function testUpdatePhone(?string $phone, ?string $expectedPhone, int $expectedStatus, ?string $expectedError): void
    {
        $user = $this->createUserForOrganisation($this->createOrganisation());
        $case = $this->createCaseForUser($user);
        $expertQuestion = $this->createExpertQuestionForCase(
            $case,
            [
                'subject' => 'My Question',
                'question' => 'Vraag?',
                'user_uuid' => $user->uuid,
                'phone' => '0118121212',
            ],
        );

        $payload = [
            'uuid' => $expertQuestion->uuid,
            'subject' => $expertQuestion->subject,
            'type' => $expertQuestion->type->value,
            'phone' => $phone,
            'question' => $expertQuestion->question,
        ];

        $response = $this->be($user)->putJson(sprintf('/api/case/%s/expertQuestion/%s', $case->uuid, $expertQuestion->uuid), $payload);

        $response->assertStatus($expectedStatus);

        $json = $response->json();

        if ($expectedPhone !== null) {
            $this->assertSame($expectedPhone, $json['phone']);
        }

        if ($expectedError !== null) {
            $this->assertArrayHasKey($expectedError, Arr::dot($json));
        }
    }

    public static function phoneTestProvider(): array
    {
        return [
            ['0612312312', '0612312312', 200, null],
            ['', null, 200, null],
            [null, null, 200, null],
            ['123123', null, 422, 'validationResult.fatal.errors.phone.0'],
        ];
    }

    #[DataProvider('supervisionCaseAccessProvider')]
    public function testSupervisionCaseAccess(
        string $roles,
        string $caseCreatedByOrganisationUuid,
        string $userOrganisationUuid,
        ?string $assignedOrganisationUuid,
        ExpertQuestionType $questionType,
        bool $pickedUp,
        bool $allowed,
    ): void {
        $this->createOrganisation(['uuid' => 'ggd1', 'type' => OrganisationType::regionalGGD()]);
        $this->createOrganisation(['uuid' => 'ggd2', 'type' => OrganisationType::regionalGGD()]);
        $this->createOrganisation(['uuid' => 'ls1', 'type' => OrganisationType::outsourceOrganisation()]);
        $this->createOrganisation(['uuid' => 'ls2', 'type' => OrganisationType::outsourceDepartment()]);

        $expertUser = $this->createUserForOrganisation(EloquentOrganisation::find($userOrganisationUuid), [], $roles);
        $case = $this->createCaseForOrganisation(
            EloquentOrganisation::find($caseCreatedByOrganisationUuid),
            ['assigned_organisation_uuid' => $assignedOrganisationUuid],
        );
        $task = $this->createTaskForCase($case, ['category' => ContactCategory::cat1()]);
        $context = $this->createContextForCase($case);
        $this->createNoteForCase($case);

        $this->createExpertQuestionForCase($case, ['type' => $questionType, 'assigned_user_uuid' => $pickedUp ? $expertUser->uuid : null]);

        $response = $this->be($expertUser)->getJson(sprintf('/api/case/%s/', $case->uuid));
        $taskResponse = $this->be($expertUser)->getJson(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $contextFragmentsResponse = $this->be($expertUser)->getJson(sprintf('/api/contexts/%s/fragments', $context->uuid));
        $contextFragmentResponse = $this->be($expertUser)->getJson(sprintf('/api/contexts/%s/fragments/general', $context->uuid));
        $contextSectionResponse = $this->be($expertUser)->getJson(sprintf('/api/contexts/%s/sections', $context->uuid));
        $noteResponse = $this->be($expertUser)->getJson(sprintf('/api/cases/%s/timeline', $case->uuid));
        $messagesResponse = $this->be($expertUser)->getJson(sprintf('/api/cases/%s/messages', $case->uuid));

        if ($allowed) {
            $response->assertStatus(200);
            $taskResponse->assertStatus(200);
            $contextFragmentsResponse->assertStatus(200);
            $contextFragmentResponse->assertStatus(200);
            $contextSectionResponse->assertStatus(200);
            $noteResponse->assertStatus(200);
            $messagesResponse->assertStatus(200);
        } else {
            $this->assertContains($response->getStatusCode(), [404, 403]);
            $taskResponse->assertStatus(403);
            $contextFragmentsResponse->assertStatus(403);
            $contextFragmentResponse->assertStatus(403);
            $contextSectionResponse->assertStatus(403);
            $this->assertContains($noteResponse->getStatusCode(), [404, 403]);
            $this->assertContains($messagesResponse->getStatusCode(), [404, 403]);
        }
    }

    public static function supervisionCaseAccessProvider(): array
    {
        return [
            'coach can see cases with a question of type "coach" and case is assigned to user organisation and picked up by the coach' =>
                ['conversation_coach', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::conversationCoach(), true, true],
            'coach can not see cases with a question of type "coach" and case is assigned to user organisation if the question is not picked up' =>
                ['conversation_coach', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::conversationCoach(), false, false],
            'coach can not see cases with a question of type "medicalSupervision" even if the case is assigned to user organisation' =>
                ['conversation_coach', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::medicalSupervision(), true, false],
            'coach can not see cases assigned to another organisation even if the type is"coach"' =>
                ['conversation_coach', 'ggd1', 'ggd1', 'ggd2', ExpertQuestionType::conversationCoach(), true, false],

            'coach_nationwide can see cases with a question of type "coach" and case is assigned to user organisation and is picked up by the coach_nationwide' =>
                ['conversation_coach_nationwide', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::conversationCoach(), true, true],
            'coach_nationwide can not see cases with a question of type "coach" and case is assigned to user organisation if the question is not picked up' =>
                ['conversation_coach_nationwide', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::conversationCoach(), false, false],
            'coach_nationwide can not see cases with a question of type "medicalSupervision" even if the case is assigned to user organisation' =>
                ['conversation_coach_nationwide', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::medicalSupervision(), true, false],
            'coach_nationwide can not see cases assigned to another organisation' =>
                ['conversation_coach_nationwide', 'ggd1', 'ggd1', 'ggd2', ExpertQuestionType::conversationCoach(), true, false],

            'medical_supervisor can see cases with a question of type "medical-supervision" and case is assigned to user organisation and is picked up by medical_supervisor' =>
                ['medical_supervisor', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::medicalSupervision(), true, true],
            'medical_supervisor can not see cases with a question of type "coach" even if the case is assigned to user organisation' =>
                ['medical_supervisor', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::conversationCoach(), true, false],
            'medical_supervisor can not see cases assigned to another organisation' =>
                ['medical_supervisor', 'ggd1', 'ggd1', 'ggd2', ExpertQuestionType::medicalSupervision(), true, false],

            'medical-supervision-nationwide can see a case if it has a question of type medical-supervision and is assigned to any non-regionalGGD, case created by ls1' =>
                ['medical_supervisor_nationwide', 'ls1', 'ls1', 'ls2', ExpertQuestionType::medicalSupervision(), true, true], // this is an invalid scenario, ls1 cannot create cases. Test anyway.

            // This is currently not supported because the CaseAuthScope is not removed @see DBCO-4285
//            'medical-supervision-nationwide can see a case if it has a question of type medical-supervision and is assigned to any non-regionalGGD and question is picked up by user' =>
//                ['medical_supervisor_nationwide', 'ggd1', 'ls1', 'ls2', ExpertQuestionType::medicalSupervision(),true,  true],
            'medical-supervision-nationwide can not see a case if it has a question of type medical-supervision and is assigned to any non-regionalGGD but is not picked up by user' =>
                ['medical_supervisor_nationwide', 'ggd1', 'ls1', 'ls2', ExpertQuestionType::medicalSupervision(),false, false],
            'medical-supervision-nationwide can not see a case if it has a question of type medical-supervision but is assigned to a regionalGGD' =>
                ['medical_supervisor_nationwide', 'ggd1', 'ls1', 'ggd1', ExpertQuestionType::medicalSupervision(), true, false],
            'medical-supervision-nationwide can not see a case if it has a question of type medical-supervision but is not uitbesteed' =>
                ['medical_supervisor_nationwide', 'ggd1', 'ls1', null, ExpertQuestionType::medicalSupervision(), true, false],
            'medical-supervision-nationwid can not see a case if it has a question of type "coach"' =>
                ['medical_supervisor_nationwide', 'ggd1', 'ls1', 'ls2', ExpertQuestionType::conversationCoach(), true, false],

            'if a user has the role medical_supervisor_nationwide AND the role conversation_coach_nationwide it should not grant access to cases with the type conversation_coach on other LSes' =>
                ['conversation_coach_nationwide,medical_supervisor_nationwide', 'ls1', 'ls1', 'ls2', ExpertQuestionType::conversationCoach(), true, false],
            'if a user has the role conversation_coach AND the role medical_supervisor it should be able to view cases with a question with the type coach' =>
                ['conversation_coach,medical_supervisor', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::conversationCoach(),true, true],
            'if a user has the role conversation_coach AND the role medical_supervisor it should be able to view cases with a question with the type supervisor' =>
                ['conversation_coach,medical_supervisor', 'ggd1', 'ggd1', 'ggd1', ExpertQuestionType::medicalSupervision(),true, true],

            'coach can see cases with a question of type "coach" and case is not uitbesteed' =>
                ['conversation_coach', 'ggd1', 'ggd1', null, ExpertQuestionType::conversationCoach(),true, true],
        ];
    }

    public function testUserIsLoggedInWithoutParametersFails(): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->postJson('/api/expert-questions/find-by-case-id');

        $response->assertStatus(403);
    }

    public function testMedicalSupervisorIsLoggedInWithoutParametersFails(): void
    {
        $user = $this->createUser([], 'medical_supervisor');

        $response = $this->be($user)->postJson('/api/expert-questions/find-by-case-id');

        $response->assertStatus(422);
    }

    public function testMedicalSupervisorIsLoggedInWithCaseId(): void
    {
        $caseId = (string) $this->faker->randomNumber(7);
        $user = $this->createUser([], 'medical_supervisor');
        $case = $this->createCaseForUser($user, ['case_id' => $caseId]);
        $expertQuestion = $this->createExpertQuestionForCase($case, [
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);

        $response = $this->be($user)->postJson('/api/expert-questions/find-by-case-id', [
            'case_id' => $caseId,
            'expert_question_type' => $expertQuestion->type->value,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($expertQuestion->uuid, $response->json('uuid'));
    }

    public function testMedicalSupervisorIsLoggedInWithCaseIdWithoutExpertQuestionType(): void
    {
        $caseId = (string) $this->faker->randomNumber(7);
        $user = $this->createUser([], 'medical_supervisor');
        $case = $this->createCaseForUser($user, ['case_id' => $caseId]);
        $expertQuestion = $this->createExpertQuestionForCase($case, [
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);

        $response = $this->be($user)->postJson('/api/expert-questions/find-by-case-id', [
            'case_id' => $caseId,
            'expert_question_type' => $expertQuestion->type->value,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($expertQuestion->uuid, $response->json()['uuid']);
    }

    public function testMedicalSupervisorIsLoggedInAndSearchesForBcoPortalNumber(): void
    {
        $bcoPortalNumber = Str::upper($this->faker->lexify('??')) . $this->faker->numerify('#-###-###');
        $user = $this->createUser([], 'medical_supervisor');
        $case = $this->createCaseForUser($user, ['case_id' => $bcoPortalNumber]);
        $expertQuestion = $this->createExpertQuestionForCase($case, [
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);

        $response = $this->be($user)->postJson('/api/expert-questions/find-by-case-id', [
            'case_id' => $bcoPortalNumber,
            'expert_question_type' => $expertQuestion->type->value,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($expertQuestion->uuid, $response->json()['uuid']);
    }

    public function testMedicalSupervisorIsLoggedInAndSearchesForHpZoneNumber(): void
    {
        $hpZoneNumber = (string) $this->faker->randomNumber(7);
        $user = $this->createUser([], 'medical_supervisor');
        $case = $this->createCaseForUser($user, ['hpzone_number' => $hpZoneNumber]);
        $expertQuestion = $this->createExpertQuestionForCase($case, [
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);

        $response = $this->be($user)->postJson('/api/expert-questions/find-by-case-id', [
            'case_id' => $hpZoneNumber,
            'expert_question_type' => $expertQuestion->type->value,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($expertQuestion->uuid, $response->json()['uuid']);
    }

    public function testMedicalSupervisorIsLoggedInAndCaseIsNotAssignedToSelf(): void
    {
        $hpZoneNumber = (string) $this->faker->randomNumber(7);
        $user = $this->createUser([], 'medical_supervisor');
        $case = $this->createCaseForUser($user, ['hpzone_number' => $hpZoneNumber]);
        $expertQuestion = $this->createExpertQuestionForCase($case, [
            'type' => ExpertQuestionType::medicalSupervision(),
            'assigned_user_uuid' => null,
        ]);

        $response = $this->be($user)->postJson('/api/expert-questions/find-by-case-id', [
            'case_id' => $hpZoneNumber,
            'expert_question_type' => $expertQuestion->type->value,
        ]);

        $response->assertStatus(200);
        $this->assertNotEquals($expertQuestion->assigned_user_uuid, $user->uuid);
    }
}
