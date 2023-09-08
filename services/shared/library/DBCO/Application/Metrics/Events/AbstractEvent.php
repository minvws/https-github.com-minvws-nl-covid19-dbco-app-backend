<?php

declare(strict_types=1);

namespace DBCO\Shared\Application\Metrics\Events;

use MinVWS\Metrics\Events\Event;

/**
 * Event.
 */
abstract class AbstractEvent implements Event
{
    public const ACTOR_STAFF = 'staff';
    public const ACTOR_INDEX = 'index';
    public const ACTOR_SYSTEM = 'system';

    /**
     * @var string
     */
    public string $type;

    /**
     * @var string
     */
    public string $actor;

    /**
     * @var string
     */
    public string $caseUuid;

    /**
     * @var string|null
     */
    public ?string $taskUuid;

    /**
     * @var string[]|null
     */
    public ?array $taskFields;

    /**
     * Constructor.
     *
     * @param string        $type
     * @param string        $actor
     * @param string        $caseUuid
     * @param string|null   $taskUuid
     * @param string[]|null $taskFields
     */
    protected function __construct(string $type, string $actor, string $caseUuid, ?string $taskUuid = null, ?array $taskFields = null)
    {
        $this->type = $type;
        $this->actor = $actor;
        $this->caseUuid = $caseUuid;
        $this->taskUuid = $taskUuid;
        $this->taskFields = $taskFields;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $data = [
            'actor' => $this->actor,
            'caseUuid' => $this->caseUuid,
        ];

        if (isset($this->taskUuid)) {
            $data['taskUuid'] = $this->taskUuid;
        }

        if (isset($this->taskFields)) {
            $data['taskFields'] = $this->taskFields;
        }

        return $data;
    }
}
