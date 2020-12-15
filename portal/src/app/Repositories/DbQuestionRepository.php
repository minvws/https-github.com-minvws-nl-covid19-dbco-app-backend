<?php

namespace App\Repositories;

use App\Models\AnswerOption;
use App\Models\Eloquent\EloquentAnswerOption;
use App\Models\Eloquent\EloquentQuestion;
use App\Models\Question;
use Illuminate\Support\Collection;

class DbQuestionRepository implements QuestionRepository
{
    public function getQuestions(string $questionnaireUuid): Collection
    {
        $dbQuestions = EloquentQuestion::where('questionnaire_uuid', $questionnaireUuid)
            ->orderBy('sort_order')
            ->get();

        $questions = [];
        foreach ($dbQuestions as $dbQuestion) {

            $answerOptions = EloquentAnswerOption::where('question_uuid', $dbQuestion->uuid)->get();
            $questions[] = $this->questionFromEloquentModel($dbQuestion, $answerOptions);
        }

        return collect($questions);
    }

    private function questionFromEloquentModel(EloquentQuestion $dbQuestion, Collection $dbAnswerOptions): Question
    {
        $question = new Question();
        $question->uuid = $dbQuestion->uuid;
        $question->label = $dbQuestion->label;
        $question->header = $dbQuestion->header;
        $question->group = $dbQuestion->group_name;
        $question->description = $dbQuestion->description;
        $question->questionType = $dbQuestion->question_type;
        $question->relevantForCategories =
            $dbQuestion->relevant_for_categories != null ?
                explode(',', $dbQuestion->relevant_for_categories) : [];

        if ($question->questionType == 'multiplechoice') {
            $answerOptions = [];
            foreach($dbAnswerOptions as $dbAnswerOption) {
                $answerOption = new AnswerOption();
                $answerOption->uuid = $dbAnswerOption->uuid;
                $answerOption->label = $dbAnswerOption->label;
                $answerOption->value = $dbAnswerOption->value;
                $answerOption->trigger = $dbAnswerOption->trigger_name;
                $answerOptions[] = $answerOption;
            }
            $question->answerOptions = $answerOptions;
        }

        return $question;
    }
}
