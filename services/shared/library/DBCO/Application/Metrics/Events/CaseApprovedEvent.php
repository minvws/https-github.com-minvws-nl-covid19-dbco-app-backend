<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Approved event.
 */
class CaseApprovedEvent extends AbstractEvent
{
    /**
     * Create completed event.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('approved', $actor, $caseUuid);
    }
}
