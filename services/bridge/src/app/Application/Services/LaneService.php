<?php
namespace DBCO\Bridge\Application\Services;

use DBCO\Bridge\Application\Models\Request;
use DBCO\Bridge\Application\Destinations\Destination;
use DBCO\Bridge\Application\Sources\Source;
use Psr\Log\LoggerInterface;
use Throwable;

class LaneService
{
    /**
     * @var Source
     */
    private Source $source;

    /**
     * @var Destination
     */
    private Destination $destination;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param Source          $source
     * @param Destination     $destination
     * @param LoggerInterface $logger
     */
    public function __construct(
        Source $source,
        Destination $destination,
        LoggerInterface $logger
    )
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->logger = $logger;
    }

    /**
     * Processes request.
     *
     * @param int $timeout
     *
     * @return bool Request processed (false on timeout).
     *
     * @throws Throwable
     */
    public function processRequest(int $timeout): bool
    {
        try {
            $this->logger->info('Wait for request');

            return $this->source->waitForRequest(
                function (Request $request) {
                    $this->logger->info('Received request with trace ID ' . $request->originTraceId);
                    $response = $this->destination->sendRequest($request);
                    $this->logger->info('Received response for trace ID ' . $request->originTraceId);
                    return $response;
                },
                $timeout
            );
        } catch (Throwable $e) {
            $this->logger->error('Error processing request: ' . $e->getMessage());
            $this->logger->debug($e->getTraceAsString());
            throw $e;
        }
    }
}
