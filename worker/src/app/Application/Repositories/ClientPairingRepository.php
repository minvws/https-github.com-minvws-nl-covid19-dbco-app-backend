<?php
namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Exceptions\PairingException;
use DBCO\Worker\Application\Exceptions\TimeoutException;
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
     * @param int $timeout
     *
     * @return PairingRequest
     *
     * @throws TimeoutException
     */
    public function waitForPairingRequest(int $timeout): PairingRequest;

    /**
     * Send pairing response to the client.
     *
     * @param PairingResponse $response
     */
    public function sendPairingResponse(PairingResponse $response);

    /**
     * Send pairing exception to the client.
     *
     * @param PairingException $exception
     */
    public function sendPairingException(PairingException $exception);
}
