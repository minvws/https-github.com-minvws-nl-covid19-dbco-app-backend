<?php
namespace DBCO\Shared\Application\Models\Metrics;

/**
 * Submitted event.
 */
class SubmittedEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('submitted', $actor, $caseUuid);
    }
}