<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Informed event.
 */
class InformedEvent extends AbstractEvent
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
        parent::__construct('informed', $actor, $caseUuid, $taskUuid);
    }
}
