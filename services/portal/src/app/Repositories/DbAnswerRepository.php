<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Answer;
use App\Models\ClassificationDetailsAnswer;
use App\Models\ContactDetailsAnswer;
use App\Models\Eloquent\EloquentAnswer;
use App\Models\IndecipherableAnswer;
use App\Models\SimpleAnswer;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Encryption\Security\CacheEntryNotFoundException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use Ramsey\Uuid\Uuid;

use function collect;

class DbAnswerRepository implements AnswerRepository
{
    public function __construct(
        private readonly EncryptionHelper $encryptionHelper,
    ) {
    }

    public function getContactDetailsAnswerByTask(string $taskUuid): ?ContactDetailsAnswer
    {
        $dbAnswer = EloquentAnswer::where('task_uuid', $taskUuid)
            ->select('answer.*', 'question.question_type')
            ->join('question', 'answer.question_uuid', '=', 'question.uuid')
            ->where('question.question_type', 'contactdetails')->first();

        if (!$dbAnswer) {
            return null;
        }

        $answer = $this->answerFromEloquentModel($dbAnswer);

        if (!$answer instanceof ContactDetailsAnswer) {
            return null;
        }

        return $answer;
    }

    public function getAllAnswersByTask(string $taskUuid): Collection
    {
        $dbAnswers = EloquentAnswer::where('task_uuid', $taskUuid)
            ->select('answer.*', 'question.question_type')
            ->join('question', 'answer.question_uuid', '=', 'question.uuid')->get();

        $answers = [];

        foreach ($dbAnswers as $dbAnswer) {
            $answers[] = $this->answerFromEloquentModel($dbAnswer);
        }

        return collect($answers);
    }

    public function createAnswer(Answer $answer): EloquentAnswer
    {
        $dbAnswer = new EloquentAnswer();
        $dbAnswer = $this->updateFromEntity($dbAnswer, $answer);
        $dbAnswer->save();
        return $dbAnswer;
    }

    public function updateAnswer(Answer $answer): EloquentAnswer
    {
        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // TODO fixme: this retrieves the object from the db, again; but eloquent won't let us easily instantiate
        // an object directly from an Answer

        /** @var EloquentAnswer $dbAnswer */
        $dbAnswer = EloquentAnswer::query()
            ->where('uuid', $answer->uuid)
            ->firstOrFail();
        $dbAnswer = $this->updateFromEntity($dbAnswer, $answer);
        $dbAnswer->save();

        return $dbAnswer;
    }

    private function answerFromEloquentModel(EloquentAnswer $dbAnswer): Answer
    {
        try {
            switch ($dbAnswer->question_type) {
                case 'contactdetails':
                    $answer = new ContactDetailsAnswer();
                    $answer->firstname = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->ctd_firstname);
                    $answer->lastname = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->ctd_lastname);
                    $answer->email = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->ctd_email);
                    $answer->phonenumber = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->ctd_phonenumber);
                    break;
                case 'classificationdetails':
                    $answer = new ClassificationDetailsAnswer();
                    $answer->value = $this->encryptionHelper->unsealOptionalStoreValue($dbAnswer->spv_value);
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

    private function updateFromEntity(EloquentAnswer $dbAnswer, Answer $answer): EloquentAnswer
    {
        $dbAnswer->uuid = $answer->uuid ?? Uuid::uuid4();
        $dbAnswer->task_uuid = $answer->taskUuid;
        $dbAnswer->question_uuid = $answer->questionUuid;

        if ($answer instanceof SimpleAnswer) {
            $dbAnswer->spv_value = $this->encryptionHelper->sealOptionalStoreValue(
                $answer->value,
                StorageTerm::short(),
                $dbAnswer->task->created_at,
            );
        } elseif ($answer instanceof ContactDetailsAnswer) {
            $dbAnswer->ctd_firstname = $this->encryptionHelper->sealOptionalStoreValue(
                $answer->firstname,
                StorageTerm::short(),
                $dbAnswer->task->created_at,
            );
            $dbAnswer->ctd_lastname = $this->encryptionHelper->sealOptionalStoreValue(
                $answer->lastname,
                StorageTerm::short(),
                $dbAnswer->task->created_at,
            );
            $dbAnswer->ctd_email = $this->encryptionHelper->sealOptionalStoreValue(
                $answer->email,
                StorageTerm::short(),
                $dbAnswer->task->created_at,
            );
            $dbAnswer->ctd_phonenumber = $this->encryptionHelper->sealOptionalStoreValue(
                $answer->phonenumber,
                StorageTerm::short(),
                $dbAnswer->task->created_at,
            );
        } elseif ($answer instanceof ClassificationDetailsAnswer) {
            $dbAnswer->spv_value = $this->encryptionHelper->sealOptionalStoreValue(
                $answer->value,
                StorageTerm::short(),
                $dbAnswer->task->created_at,
            );
        }

        return $dbAnswer;
    }
}
