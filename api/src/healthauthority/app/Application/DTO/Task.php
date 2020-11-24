<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireResult as QuestionnaireResultModel;
use DBCO\HealthAuthorityAPI\Application\Models\Task as TaskModel;
use DBCO\Shared\Application\Codable\DecodableDecorator;
use DBCO\Shared\Application\Codable\DecodingContainer;
use JsonSerializable;

/**
 * Task DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class Task implements JsonSerializable, DecodableDecorator
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
     * @inheritDoc
     */
    public static function decode(string $class, DecodingContainer $container): object
    {
        $task = new TaskModel();
        $task->uuid = strtolower($container->uuid->decodeString('uuid'));
        $task->taskType = $container->taskType->decodeString('taskType');
        $task->source = $container->source->decodeString('source');
        $task->label = $container->label->decodeString('label');
        $task->taskContext = $container->taskContext->decodeStringIfPresent('taskContext');
        $task->category = $container->category->decodeString('category');
        $task->communication = $container->communication->decodeString('communication');
        $task->dateOfLastExposure = $container->dateOfLastExposure->decodeDateTimeIfPresent('Y-m-d');
        $task->questionnaireResult =
            $container->questionnaireResult->decodeObject(QuestionnaireResultModel::class);
        return $task;
    }
}
