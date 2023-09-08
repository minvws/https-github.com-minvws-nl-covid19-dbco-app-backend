<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\ExpertQuestion\ListOptions;
use App\Repositories\ExpertQuestionRepository;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('supervision')]
class ExpertQuestionRepositoryTest extends FeatureTestCase
{
    public function testListExpertQuestions(): void
    {
        $user = $this->createUser([], 'medical_supervisor');
        $case = $this->createCaseForUser($user);
        $this->be($user);

        $this->createExpertQuestionForCase($case, [
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);

        /** @var ExpertQuestionRepository $expertQuestionRepository */
        $expertQuestionRepository = app(ExpertQuestionRepository::class);

        $listOptions = new ListOptions();
        $listOptions->type = ListOptions::TYPE_MEDICAL_SUPERVISION;
        $this->assertCount(1, $expertQuestionRepository->listExpertQuestions($listOptions)->items());
    }

    public function testListExpertQuestionsIsEmptyWhenCaseIsDeleted(): void
    {
        $user = $this->createUser([], 'medical_supervisor');
        $case = $this->createCaseForUser($user, ['case_id' => '1234567']);
        $this->be($user);

        $this->createExpertQuestionForCase($case, [
            'subject' => 'Too old question for case 1234567',
            'created_at' => CarbonImmutable::now(),
            'type' => ExpertQuestionType::medicalSupervision(),
        ]);

        $case->delete();

        /** @var ExpertQuestionRepository $expertQuestionRepository */
        $expertQuestionRepository = app(ExpertQuestionRepository::class);

        $listOptions = new ListOptions();
        $listOptions->type = ListOptions::TYPE_MEDICAL_SUPERVISION;
        $this->assertCount(0, $expertQuestionRepository->listExpertQuestions($listOptions)->items());
    }
}
