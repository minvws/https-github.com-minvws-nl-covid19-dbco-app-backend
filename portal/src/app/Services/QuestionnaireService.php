<?php

namespace App\Services;

use App\Models\SimpleAnswer;
use App\Repositories\AnswerRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\TaskRepository;
use Jenssegers\Date\Date;

class QuestionnaireService
{
    private QuestionRepository $questionRepository;
    private TaskRepository $taskRepository;
    private AnswerRepository $answerRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        TaskRepository $taskRepository,
        AnswerRepository $answerRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->taskRepository = $taskRepository;
        $this->answerRepository = $answerRepository;
    }

    public function getRobotFriendlyTaskExport(string $caseUuid): array
    {
        $tasks = $this->taskRepository->getTasks($caseUuid);

        // Hypothetically each task could've used a different questionnaire (once we have
        // more than one taks type). For now this isn't supported and we assume all tasks have
        // used the same questionnaire.
        $questions = $this->questionRepository->getQuestions($tasks->first()->questionnaireUuid);

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
                    $headers[$question->uuid.'.durationrisk'] = '> 15 min.';
                    $headers[$question->uuid.'.distancerisk'] = '< 1.5m';
                    $headers[$question->uuid.'.livedtogetherrisk'] = '> x uur zelfde huis';
                    $headers[$question->uuid.'.otherrisk'] = 'Overig risico';
                    break;
                default:
                    $headers[$question->uuid] = $question->label;
            }
            $questionTypeByQuestionUuid[$question->uuid] = $question->questionType;
        }

        $answers = $this->answerRepository->getAllAnswersByCase($caseUuid);

        $records = [];

        foreach ($tasks as $task) {
            $records[$task->uuid] = [
                'task.label' => $task->label,
                'task.source' => $task->source,
                'task.context' => $task->taskContext,
                'task.dateoflastexposure' => Date::parse($task->dateOfLastExposure)->format("Y-m-d"),
                'task.communication' => $task->communication
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
                    $records[$answer->taskUuid][$answer->questionUuid.'.durationrisk'] = $answer->durationRisk ? 'Ja' : 'Nee';
                    $records[$answer->taskUuid][$answer->questionUuid.'.distancerisk'] = $answer->distanceRisk ? 'Ja' : 'Nee';
                    $records[$answer->taskUuid][$answer->questionUuid.'.livedtogetherrisk'] = $answer->livedTogetherRisk ? 'Ja' : 'Nee';
                    $records[$answer->taskUuid][$answer->questionUuid.'.otherrisk'] = $answer->otherRisk ? 'Ja' : 'Nee';
                    break;
                default:
                    // SimpleAnswer
                    $records[$answer->taskUuid][$answer->questionUuid] = $answer->value;
            }
        }

        $answersPerTask = [];

        foreach ($tasks as $task) {
            $answersPerTask[$task->category][] = $records[$task->uuid];
        }

        // multidimensional array, rows are tasks, each column is a question-answer (referred to by question-uuid). Multi value questions are sub arrays.
        return [
            'headers' => $headers,
            'categories' => $answersPerTask
        ];
    }
}
