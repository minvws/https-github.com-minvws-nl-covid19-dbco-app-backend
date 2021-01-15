<?php

namespace App\Repositories;

use App\Models\Answer;
use App\Models\ClassificationDetailsAnswer;
use App\Models\ContactDetailsAnswer;
use App\Models\Eloquent\EloquentAnswer;
use App\Models\IndecipherableAnswer;
use App\Models\SimpleAnswer;
use App\Security\CacheEntryNotFoundException;
use App\Security\EncryptionHelper;
use Illuminate\Support\Collection;
use Jenssegers\Date\Date;
use Ramsey\Uuid\Uuid;

class DbAnswerRepository implements AnswerRepository
{
    /**
     * @var EncryptionHelper
     */
    private EncryptionHelper $encryptionHelper;

    /**
     * Constructor.
     *
     * @param EncryptionHelper $encryptionHelper
     */
    public function __construct(EncryptionHelper $encryptionHelper)
    {
        $this->encryptionHelper = $encryptionHelper;
    }

    public function getAllAnswersByCase(string $caseUuid): Collection
    {
        $dbAnswers = EloquentAnswer::where('case_uuid', $caseUuid)
            ->select('answer.*', 'question.question_type')
            ->join('task', 'answer.task_uuid', '=', 'task.uuid')
            ->join('question', 'answer.question_uuid', '=', 'question.uuid')
            ->orderBy('task.uuid')
            ->orderBy('question.sort_order')
            ->get();

        $answers = array();

        foreach($dbAnswers as $dbAnswer) {
            $answers[] = $this->answerFromEloquentModel($dbAnswer);
        };

        return collect($answers);
    }

    public function getAllAnswersByTask(string $taskUuid): Collection
    {
        $dbAnswers = EloquentAnswer::where('task_uuid', $taskUuid)
            ->select('answer.*', 'question.question_type')
            ->join('question', 'answer.question_uuid', '=', 'question.uuid')->get();

        $answers = array();

        foreach($dbAnswers as $dbAnswer) {
            $answers[] = $this->answerFromEloquentModel($dbAnswer);
        };

        return collect($answers);
    }

    public function answerFromEloquentModel(EloquentAnswer $dbAnswer): Answer
    {
        $answer = null;

        try {
            switch($dbAnswer->question_type) {
                case 'contactdetails':
                    $answer = new ContactDetailsAnswer();
                    $answer->firstname = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->ctd_firstname);
                    $answer->lastname = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->ctd_lastname);
                    $answer->email = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->ctd_email);
                    $answer->phonenumber = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->ctd_phonenumber);
                    break;
                case 'classificationdetails':
                    $answer = new ClassificationDetailsAnswer();
                    $answer->category1Risk = $dbAnswer->cfd_cat_1_risk;
                    $answer->category2ARisk = $dbAnswer->cfd_cat_2a_risk;
                    $answer->category2BRisk = $dbAnswer->cfd_cat_2b_risk;
                    $answer->category3Risk = $dbAnswer->cfd_cat_3_risk;
                    break;
                default:
                    $answer = new SimpleAnswer();
                    $answer->value = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->spv_value) ?? '';
            }
        } catch (CacheEntryNotFoundException $e) {
            $answer = new IndecipherableAnswer();
        }

        $answer->uuid = $dbAnswer->uuid;
        $answer->taskUuid = $dbAnswer->task_uuid;
        $answer->questionUuid = $dbAnswer->question_uuid;

        return $answer;
    }

    public function createAnswer(Answer $answer): void
    {
        $dbAnswer = new EloquentAnswer;
        $dbAnswer = $this->updateFromEntity($dbAnswer, $answer);

        $dbAnswer->created_at = Date::now();
        $dbAnswer->updated_at = $dbAnswer->created_at;
        $dbAnswer->save();
    }

    public function updateAnswer(Answer $answer): void
    {
        // TODO fixme: this retrieves the object from the db, again; but eloquent won't let us easily instantiate
        // an object directly from an Answer
        $dbAnswer = EloquentAnswer::where('uuid', $answer->uuid)->get()->first();

        $dbAnswer = $this->updateFromEntity($dbAnswer, $answer);
        $dbAnswer->updated_at = Date::now();
        $dbAnswer->save();
    }

    private function updateFromEntity(EloquentAnswer $dbAnswer, Answer $answer): EloquentAnswer
    {
        $dbAnswer->uuid = $answer->uuid ?? Uuid::uuid4();
        $dbAnswer->task_uuid = $answer->taskUuid;
        $dbAnswer->question_uuid = $answer->questionUuid;

        if ($answer instanceof SimpleAnswer) {
            $dbAnswer->spv_value = $this->seal($answer->value);
        } elseif ($answer instanceof ContactDetailsAnswer) {
            $dbAnswer->ctd_firstname = $this->seal($answer->firstname);
            $dbAnswer->ctd_lastname = $this->seal($answer->lastname);
            $dbAnswer->ctd_email = $this->seal($answer->email);
            $dbAnswer->ctd_phonenumber = $this->seal($answer->phonenumber);
        } elseif ($answer instanceof ClassificationDetailsAnswer) {
            $dbAnswer->cfd_cat_1_risk = $answer->category1Risk;
            $dbAnswer->cfd_cat_2a_risk = $answer->category2ARisk;
            $dbAnswer->cfd_cat_2b_risk = $answer->category2BRisk;
            $dbAnswer->cfd_cat_3_risk = $answer->category3Risk;
        }

        return $dbAnswer;
    }

    // @todo copied from another DbRepo, refactor into EncryptionHelper(?)
    private function seal(?string $value): ?string
    {
        if ($value === null) {
            return null;
        } else {
//            return $this->encryptionHelper->sealStoreValue($value);
            return $value;
        }
    }
}
