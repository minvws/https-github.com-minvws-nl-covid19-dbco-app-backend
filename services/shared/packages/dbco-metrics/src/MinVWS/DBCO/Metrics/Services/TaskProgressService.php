<?php

namespace MinVWS\DBCO\Metrics\Services;

use MinVWS\DBCO\Metrics\Repositories\TaskProgressRepository;

class TaskProgressService
{
    public const TASK_DATA_INCOMPLETE = 'incomplete';
    public const TASK_DATA_CONTACTABLE = 'contactable';
    public const TASK_DATA_COMPLETE = 'complete';

    protected TaskProgressRepository $taskProgressRepository;

    public function __construct(
        TaskProgressRepository $taskProgressRepository
    ) {
        $this->taskProgressRepository = $taskProgressRepository;
    }

    /**
     * Task completion progress is divided into three buckets to keep the UI simple:
     * - 'completed': all details are available, all questions answered
     * - 'contactable': we have enough basic data to contact the person
     * - 'incomplete': too much is still missing, provide the user UI warnings
     *
     * @param string $taskUuid
     * @return string progress constant
     */
    public function getProgress(string $taskUuid): string
    {
        $taskProgress = self::TASK_DATA_INCOMPLETE;

        $taskData = $this->taskProgressRepository->getTaskData($taskUuid);
        if (count($taskData) === 0) {
            //Error: task not found
            return $taskProgress;
        }

        if (!$this->hasClassificationAndExposureDate($taskData)) {
            return $taskProgress;
        }

        $fieldProgressCompletion = $this->fieldProgressCompletion($taskData);

        if ($this->isContactable($taskData['category'], $fieldProgressCompletion)) {
            $taskProgress = self::TASK_DATA_CONTACTABLE;
        }

        if ($this->allAnswersCompleted($taskData, $fieldProgressCompletion)) {
            // No relevant questions were skipped or unanswered: questionnaire complete!
            $taskProgress = self::TASK_DATA_COMPLETE;
        }

        return $taskProgress;
    }

    /**
     * Is classification and exposure date is present in task
     */
    private function hasClassificationAndExposureDate(array $taskData): bool
    {
        if (empty($taskData['category'])) {
            return false;
        }

        if (empty($taskData['date_of_last_exposure'])) {
            return false;
        }

        return true;
    }

    private function fieldProgressCompletion(array $taskData): FieldProgressCompletion
    {
        $fieldProgressCompletion = FieldProgressCompletion::create(['firstname', 'lastname', 'phone', 'email']);

        if (is_array($taskData['general'])) {
            $fieldProgressCompletion->completeCheck('firstname', $taskData['general']['firstname']);
            $fieldProgressCompletion->completeCheck('lastname', $taskData['general']['lastname']);
            $fieldProgressCompletion->completeCheck('phone', $taskData['general']['phone']);
            $fieldProgressCompletion->completeCheck('email', $taskData['general']['email']);
        }

        $questionsAndAnswers = $taskData['questions'];
        foreach ($questionsAndAnswers as $questionAndAnswer) {
            if ($questionAndAnswer['question_type'] !== 'contactdetails') {
                continue;
            }

            $fieldProgressCompletion->completeCheck('firstname', $questionAndAnswer['ctd_firstname']);
            $fieldProgressCompletion->completeCheck('lastname', $questionAndAnswer['ctd_lastname']);
            $fieldProgressCompletion->completeCheck('phone', $questionAndAnswer['ctd_phonenumber']);
            $fieldProgressCompletion->completeCheck('email', $questionAndAnswer['ctd_email']);
        }

        return $fieldProgressCompletion;
    }

    /**
     * Is the contact contactable based on the answers in the contactdetails question.
     *
     * @param string $category
     * @param FieldProgressCompletion $fieldProgressCompletion
     * @return bool
     */
    private function isContactable(string $category, FieldProgressCompletion $fieldProgressCompletion): bool
    {
        if (substr($category, 0, 1) === '3') {
            return $fieldProgressCompletion->isComplete('email');
        }

        if ($fieldProgressCompletion->isComplete('firstname', 'phone')) {
            return true;
        }

        if ($fieldProgressCompletion->isComplete('lastname', 'phone')) {
            return true;
        }

        return false;
    }

    /**
     * Returns true when all answers are answered. Classification details are
     * not checked because the frontend portal does not store this answer.
     *
     * @param array $taskData
     * @param FieldProgressCompletion $fieldProgressCompletion
     * @return bool
     */
    private function allAnswersCompleted(array $taskData, FieldProgressCompletion $fieldProgressCompletion): bool
    {
        if ($fieldProgressCompletion->isComplete()) {
            return true;
        }

        if (count($taskData['questions']) === 1 && $taskData['questions'][0]['question_type'] === null) {
            // Means there are no questions configured to this task and should be considered incomplete.
            return false;
        }

        // Any missed question will mark the Task partially-complete.
        foreach ($taskData['questions'] as $questionAndAnswer) {
            if (
                $questionAndAnswer['question_type'] !== 'classificationdetails' &&
                in_array($taskData['category'], $questionAndAnswer['relevant_for_categories']) &&
                (!$this->isAnswerComplete($questionAndAnswer))
            ) {
                // One missed answer: move on to next task
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if answer is complete based on the question_type.
     *
     * @param array $questionAndAnswer
     * @return bool
     */
    private function isAnswerComplete(array $questionAndAnswer): bool
    {
        switch ($questionAndAnswer['question_type']) {
            case 'contactdetails':
                return
                    !empty($questionAndAnswer['ctd_firstname']) &&
                    !empty($questionAndAnswer['ctd_lastname']) &&
                    !empty($questionAndAnswer['ctd_email']) &&
                    !empty($questionAndAnswer['ctd_phonenumber']);
                break;
            default:
                return !empty($questionAndAnswer['spv_value']);
        }
    }
}
