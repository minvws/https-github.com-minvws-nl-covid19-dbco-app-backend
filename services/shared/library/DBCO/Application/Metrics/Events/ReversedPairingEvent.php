<?php

namespace DBCO\Shared\Application\Metrics\Events;

/**
 * ReversePairing event.
 */
class ReversedPairingEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('reversed-pairing', $actor, $caseUuid);
    }
}
