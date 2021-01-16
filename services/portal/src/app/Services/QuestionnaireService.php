<?php

namespace App\Services;

use App\Models\CovidCase;
use App\Models\ExportField;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\SimpleAnswer;
use App\Repositories\AnswerRepository;
use App\Repositories\QuestionnaireRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\StateRepository;
use App\Repositories\TaskRepository;
use Jenssegers\Date\Date;

class QuestionnaireService
{
    private QuestionRepository $questionRepository;
    private QuestionnaireRepository $questionnaireRepository;
    private TaskRepository $taskRepository;
    private AnswerRepository $answerRepository;
    private StateRepository $stateRepository;

    private $fieldLabels = [
        'lastname' => 'Achternaam',
        'firstname' => 'Voornaam',
        'email' => 'E-mailadres',
        'phonenumber' => 'Telefoonnummer',
        'label' => 'Naam',
        'communication' => 'Wie communiceert',
        'context' => 'Toelichting',
        'dateoflastexposure' => 'Laatste contactmoment',
    ];

    public function __construct(
        QuestionRepository $questionRepository,
        QuestionnaireRepository $questionnaireRepository,
        TaskRepository $taskRepository,
        AnswerRepository $answerRepository,
        StateRepository $stateRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->questionnaireRepository = $questionnaireRepository;
        $this->taskRepository = $taskRepository;
        $this->answerRepository = $answerRepository;
        $this->stateRepository = $stateRepository;
    }

    public function getLatestQuestionnaire(string $taskType): ?Questionnaire
    {
        $questionnaire = $this->questionnaireRepository->getLatestQuestionnaire($taskType);
        $this->enhanceWithQuestions($questionnaire);
        return $questionnaire;
    }

    public function getQuestionnaire(string $uuid): ?Questionnaire
    {
        $questionnaire = $this->questionnaireRepository->getQuestionnaire($uuid);
        $this->enhanceWithQuestions($questionnaire);
        return $questionnaire;
    }

    private function enhanceWithQuestions(Questionnaire $questionnaire)
    {
        $questionnaire->questions = $this->questionRepository->getQuestions($questionnaire->uuid)->all();
    }

    public function getExportFriendlyTaskExport(CovidCase $case): array
    {
        $tasks = $this->taskRepository->getTasks($case->uuid);

        $needsExport = ($case->exportedAt === null || $case->exportedAt < $case->updatedAt) &&
            ($case->copiedAt === null || $case->copiedAt < $case->updatedAt);
        if ($needsExport) {
            $this->stateRepository->clearCopiedFields($case->uuid, null);
        }

        // Hypothetically each task could've used a different questionnaire (once we have
        // more than one task type). For now this isn't supported and we assume all tasks have
        // used the same questionnaire. (Note: not all tasks might have been submitted, so it's not
        // guaranteed that the first task has the questionnaire)
        $questions = [];
        foreach ($tasks as $task) {
            if ($task->questionnaireUuid) {
                // Do we have filled out questions?
                $questions = $this->questionRepository->getQuestions($task->questionnaireUuid);
                break; // We only need one set of questions because of the 'same questionnaire' assumption.
            }
        }

        if (!count($questions)) {
            // None of the tasks has a filled out questionnaire.
            // Let's use the one they should've filled out as template for the copy/paste screen
            $questionnaire = $this->questionnaireRepository->getLatestQuestionnaire('contact');
            if ($questionnaire) {
                $questions = $this->questionRepository->getQuestions($questionnaire->uuid);
            }
        }

        $answers = $this->answerRepository->getAllAnswersByCase($case->uuid);
        $answersByTaskAndQuestion = [];
        foreach($answers as $answer) {
            $answersByTaskAndQuestion[$answer->taskUuid][$answer->questionUuid] = $answer;
        }

        $records = [];

        foreach ($tasks as $task) {

            $taskNeedsExport = ($task->exportedAt === null || $task->exportedAt < $task->updatedAt) &&
                ($task->copiedAt === null || $task->copiedAt < $task->updatedAt);

            // If we need to export, als clear previously stored copy fieldnames.
            if ($taskNeedsExport) {
                $this->stateRepository->clearCopiedFields($case->uuid, $task->uuid);
            }

            $records[$task->uuid] = [
                'uuid' => $task->uuid,
                'exportId' => $task->exportId,
                'needsExport' => $taskNeedsExport,
                'copiedFields' => $this->stateRepository->getCopiedFields($case->uuid, $task->uuid),
                'data' => [
                    'label' => new ExportField('label', $this->fieldLabels['label'], $task->label)
                ]
            ];

            foreach ($questions as $question) {
                $answer = $answersByTaskAndQuestion[$task->uuid][$question->uuid] ?? null;
                switch ($question->questionType) {
                    case 'contactdetails':
                        // ContactDetailsAnswer, turns into 4 distinct columns
                        $lastNameExportField = new ExportField('lastname', $this->fieldLabels['lastname'], $answer->lastname ?? null);
                        $records[$task->uuid]['data']['lastname'] = $lastNameExportField;
                        $records[$task->uuid]['data']['firstname'] = new ExportField('firstname', $this->fieldLabels['firstname'], $answer->firstname ?? null);
                        $records[$task->uuid]['data']['email'] = new ExportField('email', $this->fieldLabels['email'], $answer->email ?? null);
                        $records[$task->uuid]['data']['phonenumber'] = new ExportField('phonenumber', $this->fieldLabels['phonenumber'], $answer->phonenumber ?? null);

                        // Currently we only support a 'new data' indicator for the task as a whole, but if there
                        // is new data, we render that button next to the lastname field.
                        if ($records[$task->uuid]['needsExport']) {
                            $lastNameExportField->isUpdated = true;
                        }

                        if ($answer && $answer->firstname != null && $answer->lastname != null) {
                            // We should hide the task label if the user has replaced it with the full name.
                            unset($records[$task->uuid]['data']['label']);
                        }
                        break;
                    case 'classificationdetails':
                        // We don't display the classification details, they have only been used to update the category.
                        break;
                    default:
                        // SimpleAnswer
                        $records[$task->uuid]['data'][$question->header] = $this->createSimpleExportField($question, $answer->value ?? null);
                }
            }

            // Enrich
            $records[$task->uuid]['data']['context'] = new ExportField('context', $this->fieldLabels['context'], $task->taskContext);
            $records[$task->uuid]['data']['dateoflastexposure'] = new ExportField('dateoflastexposure', $this->fieldLabels['dateoflastexposure'], $task->dateOfLastExposure ?? null,
                Date::parse($task->dateOfLastExposure)->format('d-m-Y (l)') ?? null,
                Date::parse($task->dateOfLastExposure)->format('Y-m-d'));
            $records[$task->uuid]['data']['communication'] = new ExportField('communication', $this->fieldLabels['communication'], $task->communication, $task->communication == 'index' ? 'Index' : 'GGD');
        }
        $tasksPerCategory = [];

        foreach ($tasks as $task) {
            $tasksPerCategory[$task->category][] = $records[$task->uuid];
        }
        ksort($tasksPerCategory);

        return [
            'case' => [
                'needsExport' => $needsExport,
                'copiedFields' => $this->stateRepository->getCopiedFields($case->uuid, null),
            ],
            'tasks' => $tasksPerCategory
        ];
    }

    private function createSimpleExportField(Question $question, $answerValue)
    {
        if ($answerValue == null) {
            return new ExportField($question->header, $question->label, null);
        }
        switch ($question->questionType)
        {
            case 'date':
                return new ExportField(
                    $question->header, // we don't have a proper fieldname in the questionnaire
                    $question->header,
                    $answerValue,
                    Date::parse($answerValue)->format('d-m-Y (l)'),
                    Date::parse($answerValue)->format('Y-m-d')
                );
            default:
                return new ExportField(
                    $question->header,
                    $question->header,
                    $answerValue
                );
        }
    }

    public function getCopyData($tasks): string
    {
        $copy = [];
        foreach ($tasks as $task) {
            $copyRec = '';
            foreach ($task['data'] as $key => $value) {
                $copyRec .= $value->fieldLabel . ": " . ($value->displayValue ?? '-') . "\n";
            }

            $copy[] = $copyRec;
        }

        return implode("\n", $copy);
    }
}
