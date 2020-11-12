<?php
namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Models\PairingRequest;
use DBCO\Worker\Application\Models\PairingResponse;

/**
 * Pairing gateway for the client.
 *
 * @package App\Application\Repositories
 */
interface ClientPairingRepository
{
    /**
     * Wait for new pairing request from the queue.
     *
     * @return PairingRequest
     */
    public function waitForPairingRequest(): PairingRequest;

    /**
     * Send pairing response to the client.
     *
     * @param PairingResponse $response
     */
    public function sendPairingResponse(PairingResponse $response);
}
