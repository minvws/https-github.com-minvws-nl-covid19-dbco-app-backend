<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Identified event.
 */
class CreatedEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('created', $actor, $caseUuid);
    }
}
