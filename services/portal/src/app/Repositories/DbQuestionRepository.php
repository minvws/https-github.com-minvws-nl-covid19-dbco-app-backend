<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AnswerOption;
use App\Models\Eloquent\EloquentAnswerOption;
use App\Models\Eloquent\EloquentQuestion;
use App\Models\Question;
use Illuminate\Support\Collection;

use function explode;

class DbQuestionRepository implements QuestionRepository
{
    public function getQuestions(string $questionnaireUuid): Collection
    {
        /** @var Collection<int, EloquentQuestion> $dbQuestions */
        $dbQuestions = EloquentQuestion::query()
            ->where('questionnaire_uuid', $questionnaireUuid)
            ->orderBy('sort_order')
            ->get();

        return $dbQuestions->map(function (EloquentQuestion $dbQuestion) {
            /** @var Collection<int, EloquentAnswerOption> $answerOptions */
            $answerOptions = EloquentAnswerOption::query()
                ->where('question_uuid', $dbQuestion->uuid)
                ->get();

            return $this->questionFromEloquentModel($dbQuestion, $answerOptions);
        });
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
        $question->relevantForCategories = explode(',', $dbQuestion->relevant_for_categories);

        if ($question->questionType === 'multiplechoice') {
            $answerOptions = [];
            foreach ($dbAnswerOptions as $dbAnswerOption) {
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
