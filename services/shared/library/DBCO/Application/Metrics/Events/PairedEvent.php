<?php
namespace DBCO\Shared\Application\Models\Metrics;

/**
 * Paired event.
 */
class PairedEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('paired', $actor, $caseUuid);
    }
}