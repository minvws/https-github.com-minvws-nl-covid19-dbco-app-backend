<?php

namespace App\Repositories;

use App\Models\Answer;
use App\Models\ClassificationDetailsAnswer;
use App\Models\ContactDetailsAnswer;
use App\Models\Eloquent\EloquentAnswer;
use App\Models\SimpleAnswer;
use App\Security\EncryptionHelper;
use Illuminate\Support\Collection;

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
                $answer->category1Risk = ($dbAnswer->cfd_cat_1_risk === 1);
                $answer->category2ARisk = ($dbAnswer->cfd_cat_2a_risk === 1);
                $answer->category2BRisk = ($dbAnswer->cfd_cat_2b_risk === 1);
                $answer->category3Risk = ($dbAnswer->cfd_cat_3_risk === 1);
                break;
            default:
                $answer = new SimpleAnswer();
                $answer->value = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->spv_value) ?? '';
        }

        $answer->uuid = $dbAnswer->uuid;
        $answer->taskUuid = $dbAnswer->task_uuid;
        $answer->questionUuid = $dbAnswer->question_uuid;

        return $answer;
    }

    public function updateAnswer(Answer $answer): void
    {
        // TODO fixme: this retrieves the object from the db, again; but eloquent won't let us easily instantiate
        // an object directly from an Answer
        $dbAnswer = EloquentAnswer::where('uuid', $answerUuid)->get()->first();

        if ($answer instanceof SimpleAnswer) {
            $dbAnswer->value = $this->encryptionHelper->sealStoreValue($answer->value);
        } elseif ($answer instanceof ContactDetailsAnswer) {
            $dbAnswer->ctd_firstname = $this->encryptionHelper->sealStoreValue($answer->firstname);
            $dbAnswer->ctd_lastname = $this->encryptionHelper->sealStoreValue($answer->lastname);
            $dbAnswer->ctd_email = $this->encryptionHelper->sealStoreValue($answer->email);
            $dbAnswer->ctd_phonenumber = $this->encryptionHelper->sealStoreValue($answer->phonenumber);
        } elseif ($answer instanceof ClassificationDetailsAnswer) {
            $dbAnswer->category1Risk = $answer->category1Risk ? 1 : 0;
            $dbAnswer->category2ARisk = $answer->category2ARisk ? 1 : 0;
            $dbAnswer->category2BRisk = $answer->category2BRisk ? 1 : 0;
            $dbAnswer->category3Risk = $answer->category3Risk ? 1 : 0;
        }

        $dbAnswer->save();
    }
}

