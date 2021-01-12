<?php
namespace DBCO\Shared\Application\Models\Metrics;

/**
 * Inventoried event.
 */
class InventoriedEvent extends AbstractEvent
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
        parent::__construct('inventoried', $actor, $caseUuid, $taskUuid);
    }
}