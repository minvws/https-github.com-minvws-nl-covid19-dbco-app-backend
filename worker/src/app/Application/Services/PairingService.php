<?php
namespace DBCO\Worker\Application\Services;

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
     * @throws Throwable
     */
    public function processPairingQueueEntry()
    {
        try {
            $this->logger->debug('Wait for pairing request');
            $request = $this->clientPairingRepository->waitForPairingRequest();
            $this->logger->info('Received pairing request for case ' . $request->case->id);
            $this->logger->info('Send client request to health authority for case ' . $request->case->id);
            $response = $this->healthAuthorityPairingRepository->completePairing($request);
            $this->logger->debug('Send health authority response to client for case ' . $request->case->id);
            $this->clientPairingRepository->sendPairingResponse($response);
            $this->logger->debug('Successfully completed pairing for case ' . $request->case->id);
        } catch (Throwable $e) {
            $this->logger->error('Error processing pairing queue entry: ' . $e->getMessage());
            $this->logger->debug($e->getTraceAsString());
            throw $e;
        }
    }
}
