<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Identified event.
 */
class IdentifiedEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param string $actor
     * @param string $caseUuid
     * @param string $taskUuid
     */
    public function __construct(string $actor, string $caseUuid, string $taskUuid)
    {
        parent::__construct('identified', $actor, $caseUuid, $taskUuid);
    }
}
