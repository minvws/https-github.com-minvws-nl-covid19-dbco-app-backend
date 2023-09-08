<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Edited event.
 */
class EditedEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param string $actor
     * @param string $caseUuid
     * @param string $taskUuid
     * @param array  $taskFields
     */
    public function __construct(string $actor, string $caseUuid, string $taskUuid, array $taskFields)
    {
        parent::__construct('edited', $actor, $caseUuid, $taskUuid, $taskFields);
    }
}
