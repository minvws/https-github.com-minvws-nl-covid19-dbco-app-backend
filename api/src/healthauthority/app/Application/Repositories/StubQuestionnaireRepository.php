<?php
namespace App\Application\Repositories;

use App\Application\Models\ClassificationDetailsQuestion;
use App\Application\Models\DateQuestion;
use App\Application\Models\Question;
use App\Application\Models\Questionnaire;
use App\Application\Models\QuestionnaireList;

/**
 * Used for retrieving questionnaires.
 *
 * Stub implementation.
 *
 * @package App\Application\Repositories
 */
class StubQuestionnaireRepository implements QuestionnaireRepository
{
    /**
     * Returns the questionnaire list.
     *
     * @return QuestionnaireList
     */
    public function getQuestionnaires(): QuestionnaireList
    {
        $questionnaire = new Questionnaire();
        $questionnaire->id = "3fa85f64-5717-4562-b3fc-2c963f66afa6";
        $questionnaire->taskType = "contact";

        $question1 = new ClassificationDetailsQuestion();
        $question1->id = "37d818ed-9499-4b9a-9771-725467368387";
        $question1->group = "context";
        $question1->label = "Vragen over jullie ontmoeting";
        $question1->description = null;
        $question1->relevantForCategories = [ "1", "2a", "2b", "3" ];
        $questionnaire->questions[] = $question1;

        $question2 = new DateQuestion();
        $question2->id = "37d818ed-9499-4b9a-9771-725467368388";
        $question2->group = "context";
        $question2->label = "Wanneer was de laatste ontmoeting?";
        $question2->description = null;
        $question2->relevantForCategories = [ "1", "2a", "2b", "3" ];
        $questionnaire->questions[] = $question2;

        $list = new QuestionnaireList();
        $list->questionnaires[] = $questionnaire;

        return $list;
    }
}
