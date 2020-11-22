<?php
namespace DBCO\Bridge\Application\Sources;

/**
 * Process incoming requests.
 *
 * @package App\Application\Repositories
 */
interface Source
{
    /**
     * Wait for request and call callback.
     *
     * When receiving a new request the callback is called. The response of the
     * callback can be returned to the sender (depending on the implementation).
     *
     * @param callable $callback Request callback.
     * @param int      $timeout  Timeout waiting for request.
     *
     * @return bool Request received (e.g. false on timeout).
     */
    public function waitForRequest(callable $callback, int $timeout): bool;
}
