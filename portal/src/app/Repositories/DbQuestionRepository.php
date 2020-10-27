<?php

namespace App\Repositories;

use App\Models\Eloquent\EloquentQuestion;
use App\Models\Question;
use Illuminate\Support\Collection;

class DbQuestionRepository implements QuestionRepository
{
    public function getQuestions(string $questionnaireUuid): Collection
    {
        $dbQuestions = EloquentQuestion::where('questionnaire_uuid', $questionnaireUuid)->get();

        $questions = [];
        foreach ($dbQuestions as $dbQuestion) {
            $questions[] = $this->questionFromEloquentModel($dbQuestion);
        }

        return collect($questions);
    }

    private function questionFromEloquentModel(EloquentQuestion $dbQuestion): Question
    {
        $question = new Question();
        $question->uuid = $dbQuestion->uuid;
        $question->label = $dbQuestion->label;
        $question->group = $dbQuestion->group;
        $question->description = $dbQuestion->description;
        $question->questionType = $dbQuestion->question_type;
        $question->relevantForCategories =
            $dbQuestion->relevant_for_categories != null ?
                explode(',', $dbQuestion->relevant_for_categories) : [];

        return $question;
    }
}
