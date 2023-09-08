<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Opened event.
 */
class OpenedEvent extends AbstractEvent
{
    /**
     * Create opened event.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('opened', $actor, $caseUuid);
    }
}
