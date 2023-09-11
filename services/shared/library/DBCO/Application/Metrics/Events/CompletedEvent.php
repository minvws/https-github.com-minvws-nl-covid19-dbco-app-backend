<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Completed event.
 */
class CompletedEvent extends AbstractEvent
{
    /**
     * Create completed event.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('completed', $actor, $caseUuid);
    }
}
