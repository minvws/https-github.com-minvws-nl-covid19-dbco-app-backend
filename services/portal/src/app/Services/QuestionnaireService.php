<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Questionnaire;
use App\Repositories\QuestionnaireRepository;
use App\Repositories\QuestionRepository;

readonly class QuestionnaireService
{
    public function __construct(
        private QuestionRepository $questionRepository,
        private QuestionnaireRepository $questionnaireRepository,
    ) {
    }

    public function getLatestQuestionnaire(string $taskType): ?Questionnaire
    {
        $questionnaire = $this->questionnaireRepository->getLatestQuestionnaire($taskType);

        if ($questionnaire === null) {
            return null;
        }

        return $this->enhanceWithQuestions($questionnaire);
    }

    public function getQuestionnaire(string $uuid): ?Questionnaire
    {
        $questionnaire = $this->questionnaireRepository->getQuestionnaire($uuid);

        if ($questionnaire === null) {
            return null;
        }

        return $this->enhanceWithQuestions($questionnaire);
    }

    private function enhanceWithQuestions(Questionnaire $questionnaire): Questionnaire
    {
        $questionnaire->questions = $this->questionRepository->getQuestions($questionnaire->uuid)->all();

        return $questionnaire;
    }
}
