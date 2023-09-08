<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Expired event.
 */
class ExpiredEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('expired', $actor, $caseUuid);
    }
}
