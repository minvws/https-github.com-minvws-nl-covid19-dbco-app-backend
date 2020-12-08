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

    /**
     * Unseal value.
     *
     * @param string|null $sealedValue
     * 
     * @return string|null
     */
    private function unseal(?string $sealedValue): ?string
    {
        if ($sealedValue === null) {
            return null;
        } else {
            return $this->encryptionHelper->unsealStoreValue($sealedValue);
        }
    }

    public function getAllAnswersByCase(string $caseUuid): Collection
    {
        $dbAnswers = EloquentAnswer::where('case_uuid', $caseUuid)
            ->select('answer.*', 'question.question_type')
            ->join('task', 'answer.task_uuid', '=', 'task.uuid')
            ->join('question', 'answer.question_uuid', '=', 'question.uuid')->get();

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
                $answer->firstname = $this->unseal($dbAnswer->ctd_firstname);
                $answer->lastname = $this->unseal($dbAnswer->ctd_lastname);
                $answer->email = $this->unseal($dbAnswer->ctd_email);
                $answer->phonenumber = $this->unseal($dbAnswer->ctd_phonenumber);
                break;
            case 'classificationdetails':
                $answer = new ClassificationDetailsAnswer();
                $answer->category1Risk = ($dbAnswer->cfd_cat_1_risk == 1);
                $answer->category2ARisk = ($dbAnswer->cfd_cat_2a_risk == 1);
                $answer->category2BRisk = ($dbAnswer->cfd_cat_2b_risk == 1);
                $answer->category3Risk = ($dbAnswer->cfd_cat_3_risk == 1);
                break;
            default:
                $answer = new SimpleAnswer();
                $answer->value = $this->unseal($dbAnswer->spv_value) ?? '';
        }

        $answer->uuid = $dbAnswer->uuid;
        $answer->taskUuid = $dbAnswer->task_uuid;
        $answer->questionUuid = $dbAnswer->question_uuid;

        return $answer;
    }
}
