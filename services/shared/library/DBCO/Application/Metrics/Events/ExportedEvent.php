<?php
namespace DBCO\Shared\Application\Models\Metrics;

/**
 * Exported event.
 */
class ExportedEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('exported', $actor, $caseUuid);
    }
}