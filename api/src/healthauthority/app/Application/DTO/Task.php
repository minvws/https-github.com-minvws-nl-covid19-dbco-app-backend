<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\ContactDetails;
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
        $task->taskContext = $container->taskContext->decodeStringIfPresent('taskContext');
        $task->category = $container->category->decodeString('category');
        $task->communication = $container->communication->decodeString('communication');
        $task->dateOfLastExposure = $container->dateOfLastExposure->decodeDateTimeIfPresent('Y-m-d');
        $task->questionnaireResult =
            $container->questionnaireResult->decodeObjectIfPresent(QuestionnaireResultModel::class);

        if ($task->source === 'portal') {
            $task->label = $container->label->decodeString();
        } else {
            $label = '?';

            if ($task->questionnaireResult) {
                foreach ($task->questionnaireResult->answers as $answer) {
                    if ($answer->value instanceof ContactDetails && $answer->value->firstName && $answer->value->lastName) {
                        $label = $answer->value->firstName . ' ' . substr($answer->value->lastName, 0, 1);
                        break;
                    } else if ($answer->value instanceof ContactDetails && $answer->value->firstName) {
                        $label = $answer->value->firstName;
                        break;
                    }
                }
            }

            $task->label = $label;
        }

        return $task;
    }
}
