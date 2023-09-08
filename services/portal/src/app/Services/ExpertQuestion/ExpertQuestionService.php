<?php

declare(strict_types=1);

namespace App\Services\ExpertQuestion;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\ExpertQuestionAnswer;
use App\Models\ExpertQuestion\ExpertQuestionTypeRoleMap;
use App\Models\ExpertQuestion\ListOptions;
use App\Repositories\ExpertQuestionRepository;
use App\Services\Timeline\TimelineService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use function array_map;
use function in_array;

class ExpertQuestionService
{
    public function __construct(
        private readonly ExpertQuestionRepository $expertQuestionRepository,
        private readonly TimelineService $timelineService,
    ) {
    }

    public function createExpertQuestion(
        EloquentCase $case,
        EloquentUser $user,
        ExpertQuestionType $type,
        string $subject,
        ?string $phone,
        string $question,
    ): ExpertQuestion {
        $expertQuestion = $this->expertQuestionRepository->createExpertQuestion($case, $user, $type, $subject, $phone, $question);
        $this->timelineService->addToTimeline($expertQuestion);

        return $expertQuestion;
    }

    public function updateExpertQuestion(
        ExpertQuestion $expertQuestion,
        ?ExpertQuestionType $type = null,
        ?string $subject = null,
        ?string $phone = null,
        ?string $question = null,
    ): ExpertQuestion {
        if ($type !== null) {
            $expertQuestion->type = $type;
        }

        $expertQuestion->subject = $subject;
        $expertQuestion->phone = $phone;
        $expertQuestion->question = $question;

        $expertQuestion->save();

        return $expertQuestion;
    }

    public function listExpertQuestions(ListOptions $listOptions): LengthAwarePaginator
    {
        return $this->expertQuestionRepository->listExpertQuestions($listOptions);
    }

    public function getExpertQuestionById(string $uuid): ?ExpertQuestion
    {
        return $this->expertQuestionRepository->getExpertQuestionById($uuid);
    }

    public function answerExpertQuestion(ExpertQuestion $expertQuestion, string $answer, string $answeredBy): ExpertQuestion
    {
        $eloquentAnswer = new ExpertQuestionAnswer([
            'case_created_at' => $expertQuestion->case_created_at,
            'answer' => $answer,
            'answered_by' => $answeredBy,
        ]);

        return $this->expertQuestionRepository->attachAnswer($expertQuestion, $eloquentAnswer);
    }

    public function assignExpertQuestion(ExpertQuestion $expertQuestion, string $expertUserUuid): ExpertQuestion
    {
        return $this->expertQuestionRepository->assignExpertQuestionToExpertUser($expertQuestion, $expertUserUuid);
    }

    public function unassignExpertQuestion(ExpertQuestion $expertQuestion): ExpertQuestion
    {
        if ($expertQuestion->assignedUser === null) {
            throw new UnprocessableEntityHttpException('can\'t unassign expert question which isn\'t assigned');
        }

        return $this->expertQuestionRepository->unassignExpertQuestion($expertQuestion);
    }

    public function getExpertQuestionByTypeAndCaseId(string $caseId, ExpertQuestionType $expertQuestionType): ?ExpertQuestion
    {
        return $this->expertQuestionRepository->getExpertQuestionByTypeAndCaseId($caseId, $expertQuestionType);
    }

    public function canUserAccessExpertQuestion(EloquentUser $eloquentUser, ExpertQuestion $expertQuestion): bool
    {
        $expertQuestionTypes = array_map(
            static fn (ExpertQuestionType $expertQuestionType) => $expertQuestionType->value,
            ExpertQuestionTypeRoleMap::getExpertQuestionTypesForRoles($eloquentUser->getRolesArray()),
        );

        return in_array($expertQuestion->type->value, $expertQuestionTypes, true);
    }
}
