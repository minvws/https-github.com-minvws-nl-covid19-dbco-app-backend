<?php
namespace DBCO\Worker\Application\Services;

use DBCO\Worker\Application\Exceptions\PairingException;
use DBCO\Worker\Application\Exceptions\TimeoutException;
use DBCO\Worker\Application\Repositories\ClientPairingRepository;
use DBCO\Worker\Application\Repositories\HealthAuthorityPairingRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Throwable;

class PairingService
{
    /**
     * @var ClientPairingRepository
     */
    private ClientPairingRepository $clientPairingRepository;

    /**
     * @var HealthAuthorityPairingRepository
     */
    private HealthAuthorityPairingRepository $healthAuthorityPairingRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param ClientPairingRepository          $clientPairingRepository
     * @param HealthAuthorityPairingRepository $healthAuthorityPairingRepository
     * @param LoggerInterface                  $logger
     */
    public function __construct(
        ClientPairingRepository $clientPairingRepository,
        HealthAuthorityPairingRepository $healthAuthorityPairingRepository,
        LoggerInterface $logger
    )
    {
        $this->clientPairingRepository = $clientPairingRepository;
        $this->healthAuthorityPairingRepository = $healthAuthorityPairingRepository;
        $this->logger = $logger;
    }

    /**
     * Processes a single pairing queue entry.
     *
     * @param int $timeout Timeout.
     *
     * @throws Throwable
     */
    public function processPairingQueueEntry(int $timeout)
    {
        try {
            $this->logger->debug('Wait for pairing request (timeout: ' . $timeout . 's)');
            $request = $this->clientPairingRepository->waitForPairingRequest($timeout);
            $this->logger->info('Received pairing request for case ' . $request->case->id);
            $this->logger->info('Send client request to health authority for case ' . $request->case->id);
            $response = $this->healthAuthorityPairingRepository->completePairing($request);
            $this->logger->debug('Send health authority response to client for case ' . $request->case->id);
            $this->clientPairingRepository->sendPairingResponse($response);
            $this->logger->debug('Successfully completed pairing for case ' . $request->case->id);
        } catch (PairingException $e) {
            $this->logger->debug('Pairing failed for case ' . $request->case->id);
            $this->clientPairingRepository->sendPairingException($e);
        } catch (TimeoutException $e) {
            $this->logger->debug('Timeout waiting for pairing request');
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error('Error processing pairing queue entry: ' . $e->getMessage());
            $this->logger->debug($e->getTraceAsString());
            throw $e;
        }
    }
}
