<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DateTime;
use  DBCO\HealthAuthorityAPI\Application\Models\Task as TaskModel;
use JsonSerializable;
use stdClass;

/**
 * Task DTO.
 *
 * @package  DBCO\HealthAuthorityAPI\Application\Actions
 */
class Task implements JsonSerializable
{
    /**
     * @var TaskModel $task
     */
    private TaskModel $task;

    /**
     * @var QuestionnaireResult|null
     */
    private ?QuestionnaireResult $questionnaireResult;

    /**
     * Constructor.
     *
     * @param TaskModel $task
     */
    public function __construct(TaskModel $task)
    {
        $this->task = $task;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => $this->task->uuid,
            'taskType' => $this->task->taskType,
            'source' => $this->task->source,
            'label' => $this->task->label,
            'taskContext' => $this->task->taskContext,
            'category' => $this->task->category,
            'communication' => $this->task->communication,
            'dateOfLastExposure' =>
                $this->task->dateOfLastExposure !== null ? $this->task->dateOfLastExposure->format('Y-m-d') : null
        ];
    }

    /**
     * Unserialize from JSON data structure.
     *
     * @param stdClass $data
     *
     * @return Task
     */
    public static function jsonUnserialize(stdClass $data): TaskModel
    {
        $task = new TaskModel();
        $task->uuid = $data->uuid;
        $task->taskType = $data->taskType;
        $task->source = $data->source;
        $task->label = $data->label;
        $task->taskContext = $data->taskContext ?? null;
        $task->category = $data->category;
        $task->communication = $data->communication;

        $dateOfLastExposure = $data->dateOfLastExposure ?? null;
        $task->dateOfLastExposure =
            $dateOfLastExposure != null ? DateTime::createFromFormat('Y-m-d', $dateOfLastExposure) : null;

        $questionnaireResult = $data->questionnaireResult ?? null;
        if (!empty($questionnaireResult)) {
            $task->questionnaireResult = QuestionnaireResult::jsonUnserialize($questionnaireResult);
        } else {
            $task->questionnaireResult = null;
        }

        return $task;
    }
}
