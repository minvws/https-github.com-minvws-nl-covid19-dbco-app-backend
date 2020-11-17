<?php

namespace App\Services;

use App\Models\Questionnaire;
use App\Models\SimpleAnswer;
use App\Repositories\AnswerRepository;
use App\Repositories\QuestionnaireRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\TaskRepository;
use Jenssegers\Date\Date;

class QuestionnaireService
{
    private QuestionRepository $questionRepository;
    private QuestionnaireRepository $questionnaireRepository;
    private TaskRepository $taskRepository;
    private AnswerRepository $answerRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        QuestionnaireRepository $questionnaireRepository,
        TaskRepository $taskRepository,
        AnswerRepository $answerRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->questionnaireRepository = $questionnaireRepository;
        $this->taskRepository = $taskRepository;
        $this->answerRepository = $answerRepository;
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

    public function getRobotFriendlyTaskExport(string $caseUuid): array
    {
        $tasks = $this->taskRepository->getTasks($caseUuid);

        // Hypothetically each task could've used a different questionnaire (once we have
        // more than one task type). For now this isn't supported and we assume all tasks have
        // used the same questionnaire.
        $task = $tasks->first();
        $questions = [];
        if ($task && $task->questionnaireUuid) {
            // Do we have filled out questions?
            $questions = $this->questionRepository->getQuestions($task->questionnaireUuid);
        }
        $questionTypeByQuestionUuid = [];

        $headers = [
            'task.label' => 'Naam',
            'task.source' => 'Bron',
            'task.context' => 'Context',
            'task.dateoflastexposure' => 'Laatste contact',
            'task.communication' => 'Wie informeert'
        ];
        foreach($questions as $question)
        {
            switch ($question->questionType) {
                case 'contactdetails':
                    $headers[$question->uuid.'.firstname'] = 'Voornaam';
                    $headers[$question->uuid.'.lastname'] = 'Achternaam';
                    $headers[$question->uuid.'.email'] = 'Email';
                    $headers[$question->uuid.'.phonenumber'] = 'Telefoon';
                    break;
                case 'classificationdetails':
                    $headers[$question->uuid.'.category1risk'] = 'Cat 1 risico';
                    $headers[$question->uuid.'.category2arisk'] = 'Cat 2a risico';
                    $headers[$question->uuid.'.category2brisk'] = 'Cat 2b risico';
                    $headers[$question->uuid.'.category3risk'] = 'Cat 3 risico';
                    break;
                default:
                    $headers[$question->uuid] = $question->header ?? $question->label;
            }
            $questionTypeByQuestionUuid[$question->uuid] = $question->questionType;
        }

        $answers = $this->answerRepository->getAllAnswersByCase($caseUuid);

        $records = [];

        foreach ($tasks as $task) {
            $records[$task->uuid] = [
                'task.uuid' => $task->uuid,
                'task.label' => $task->label,
                'task.source' => $task->source,
                'task.context' => $task->taskContext,
                'task.dateoflastexposure' => $task->dateOfLastExposure !== null ? Date::parse($task->dateOfLastExposure)->format("Y-m-d"): '',
                'task.communication' => $task->communication,
                'task.exportId' => $task->exportId,
                'task.enableExport' => $task->exportedAt === null || $task->exportedAt < $task->updatedAt
            ];

        }

        foreach ($answers as $answer) {
            switch($questionTypeByQuestionUuid[$answer->questionUuid]) {
                case 'contactdetails':
                    // ContactDetailsAnswer, turns into 4 distinct columns
                    $records[$answer->taskUuid][$answer->questionUuid.'.firstname'] = $answer->firstname;
                    $records[$answer->taskUuid][$answer->questionUuid.'.lastname'] = $answer->lastname;
                    $records[$answer->taskUuid][$answer->questionUuid.'.email'] = $answer->email;
                    $records[$answer->taskUuid][$answer->questionUuid.'.phonenumber'] = $answer->phonenumber;
                    break;
                case 'classificationdetails':
                    // ClassificationDetailsAnswer, turns into 4 distinct columns
                    $records[$answer->taskUuid][$answer->questionUuid.'.category1risk'] = $answer->category1Risk ? 'Ja' : 'Nee';
                    $records[$answer->taskUuid][$answer->questionUuid.'.category2arisk'] = $answer->category2ARisk ? 'Ja' : 'Nee';
                    $records[$answer->taskUuid][$answer->questionUuid.'.category2brisk'] = $answer->category2BRisk ? 'Ja' : 'Nee';
                    $records[$answer->taskUuid][$answer->questionUuid.'.category3risk'] = $answer->category3Risk ? 'Ja' : 'Nee';
                    break;
                default:
                    // SimpleAnswer
                    $records[$answer->taskUuid][$answer->questionUuid] = $answer->value;
            }
        }

        $tasksPerCategory = [];

        foreach ($tasks as $task) {
            $tasksPerCategory[$task->category][] = $records[$task->uuid];
        }
        ksort($tasksPerCategory);

        // multidimensional array, rows are tasks, each column is a question-answer (referred to by question-uuid). Multi value questions are sub arrays.
        return [
            'headers' => $headers,
            'categories' => $tasksPerCategory
        ];
    }
}
