<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\Timeline;
use App\Services\ExpertQuestion\ExpertQuestionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('supervision')]
class ExpertQuestionServiceTest extends FeatureTestCase
{
    public function testCreateExpertQuestion(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        /** @var ExpertQuestionService $expertQuestionService */
        $expertQuestionService = app(ExpertQuestionService::class);

        $this->assertDatabaseMissing('expert_question', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
            'type' => ExpertQuestionType::conversationCoach()->value,
        ]);

        $expertQuestionService->createExpertQuestion(
            $case,
            $user,
            ExpertQuestionType::conversationCoach(),
            'Mý very difficult question',
            null,
            'lorem.
            ipsum dummy text',
        );

        $this->assertDatabaseHas('expert_question', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
            'type' => ExpertQuestionType::conversationCoach()->value,
        ]);

        /** @var ExpertQuestion $question */
        $question = ExpertQuestion::where('case_uuid', $case->uuid)->where('user_uuid', $user->uuid)->sole();
        $this->assertSame('Mý very difficult question', $question->subject);
        $this->assertSame('lorem.
            ipsum dummy text', $question->question);
        $this->assertNull($question->phone);

        /** @var object $rs */
        $rs = DB::table('expert_question')->where('uuid', $question->uuid)->sole();
        $this->assertStringContainsString('ciphertext', $rs->subject);
        $this->assertStringContainsString('ciphertext', $rs->question);
        $this->assertNull($rs->phone);

        $this->assertDatabaseHas(Timeline::class, [
            'timelineable_id' => $question->uuid,
            'timelineable_type' => 'expert-question',
        ]);
    }

    public function testUpdateExpertQuestion(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $expertQuestion = $this->createExpertQuestionForCase($case);

        /** @var ExpertQuestionService $expertQuestionService */
        $expertQuestionService = app(ExpertQuestionService::class);
        $expertQuestionService->updateExpertQuestion(
            $expertQuestion,
            ExpertQuestionType::medicalSupervision(),
            'This is a sûbject',
            '06123123',
            'My very long question',
        );

        $this->assertEquals(ExpertQuestionType::medicalSupervision(), $expertQuestion->type);
        $this->assertSame('This is a sûbject', $expertQuestion->subject);
        $this->assertSame('My very long question', $expertQuestion->question);
        $this->assertSame('06123123', $expertQuestion->phone);

        $updatedQuestion = $expertQuestionService->getExpertQuestionById($expertQuestion->uuid);
        $this->assertEquals(ExpertQuestionType::medicalSupervision(), $updatedQuestion->type);
        $this->assertSame('This is a sûbject', $updatedQuestion->subject);
        $this->assertSame('My very long question', $updatedQuestion->question);
        $this->assertSame('06123123', $updatedQuestion->phone);

        /** @var object $rs */
        $rs = DB::table('expert_question')->where('uuid', $expertQuestion->uuid)->sole();
        $this->assertStringContainsString('ciphertext', $rs->subject);
        $this->assertStringContainsString('ciphertext', $rs->question);
        $this->assertStringContainsString('ciphertext', $rs->phone);
    }

    public function testGetExpertQuestionWithAuthorizationByCaseId(): void
    {
        $user = $this->createUser([], 'medical_supervisor|conversation_coach');
        $case1 = $this->createCaseForUser($user, ['case_id' => '1111111']);
        $case2 = $this->createCaseForUser($user, ['case_id' => '2222222']);

        $expertQuestion1 = $this->createExpertQuestionForCase($case1, [
            'subject' => 'Conversation coach question for case 1111111',
            'type' => ExpertQuestionType::conversationCoach(),
        ]);
        $expertQuestion2 = $this->createExpertQuestionForCase($case1, [
            'subject' => 'Medical supervision question for case 1111111',
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);
        $expertQuestion3 = $this->createExpertQuestionForCase($case2, [
            'subject' => 'Medical supervision question for case 2222222 from yesterday',
            'created_at' => CarbonImmutable::now()->subDay(),
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);
        $expertQuestion4 = $this->createExpertQuestionForCase($case2, [
            'subject' => 'Medical supervision question for case 2222222 from just now',
            'created_at' => CarbonImmutable::now(),
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);

        /** @var ExpertQuestionService $expertQuestionService */
        $expertQuestionService = app(ExpertQuestionService::class);

        $this->assertEquals(
            $expertQuestion1->subject,
            $expertQuestionService->getExpertQuestionByTypeAndCaseId('1111111', ExpertQuestionType::conversationCoach())->subject,
        );

        $this->assertEquals(
            $expertQuestion2->subject,
            $expertQuestionService->getExpertQuestionByTypeAndCaseId('1111111', ExpertQuestionType::medicalSupervision())->subject,
        );

        $actual = $expertQuestionService->getExpertQuestionByTypeAndCaseId('2222222', ExpertQuestionType::medicalSupervision());

        $this->assertEquals($expertQuestion3->subject, $actual->subject);
        $this->assertNotEquals($expertQuestion4->subject, $actual->subject);

        $this->assertNull($expertQuestionService->getExpertQuestionByTypeAndCaseId('2222222', ExpertQuestionType::conversationCoach()));
        $this->assertNull($expertQuestionService->getExpertQuestionByTypeAndCaseId('0000000', ExpertQuestionType::medicalSupervision()));
    }

    public function testGetExpertQuestionWithAuthorizationByCaseIdOlderThanANumberOfDays(): void
    {
        $user = $this->createUser([], 'medical_supervisor');
        $case1 = $this->createCaseForUser($user, ['case_id' => '1234567']);
        $case2 = $this->createCaseForUser($user, ['case_id' => '8888888']);

        $this->createExpertQuestionForCase($case1, [
            'subject' => 'Too old question for case 1234567',
            'created_at' => CarbonImmutable::now()->subDays(5),
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);
        $this->createExpertQuestionForCase($case2, [
            'subject' => 'Old but not too old supervision question for case 8888888',
            'created_at' => CarbonImmutable::now()->subDays(4),
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);

        /** @var ExpertQuestionService $expertQuestionService */
        $expertQuestionService = app(ExpertQuestionService::class);

        $this->assertNull($expertQuestionService->getExpertQuestionByTypeAndCaseId('1234567', ExpertQuestionType::medicalSupervision()));
        $this->assertNotNull($expertQuestionService->getExpertQuestionByTypeAndCaseId('8888888', ExpertQuestionType::medicalSupervision()));
    }
}
