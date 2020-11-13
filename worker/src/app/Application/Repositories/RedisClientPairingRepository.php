<?php
namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Exceptions\PairingException;
use DBCO\Worker\Application\Exceptions\TimeoutException;
use DBCO\Worker\Application\Models\PairingRequest;
use DBCO\Worker\Application\Models\PairingRequestCase;
use DBCO\Worker\Application\Models\PairingResponse;
use Exception;
use Predis\Client as PredisClient;
use Predis\Connection\ConnectionException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * Pairing gateway for the client.
 *
 * @package App\Application\Repositories
 */
class RedisClientPairingRepository implements ClientPairingRepository
{
    private const PAIRING_LIST_KEY = 'pairings';
    private const PAIRING_RESPONSE_LIST_KEY_TEMPLATE = 'pairing-response:%s';

    /**
     * Redis client.
     *
     * @var PredisClient
     */
    private PredisClient $client;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param PredisClient    $client
     * @param LoggerInterface $logger
     */
    public function __construct(PredisClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function waitForPairingRequest(int $timeout): PairingRequest
    {
        $end = time() + $timeout;

        while (true) {
            if ($end <= time()) {
                throw new TimeoutException();
            }

            try {
                $result = $this->client->blpop(self::PAIRING_LIST_KEY, $end - time());
                if (!is_array($result) || count($result) !== 2) {
                    continue;
                }

                $data = json_decode($result[1]);

                $case = new PairingRequestCase($data->case->id);
                $sealedClientPublicKey = $data->sealedClientPublicKey;

                return new PairingRequest($case, $sealedClientPublicKey);
            } catch (ConnectionException $e) {
                $this->logger->error('Redis connection error: ' . $e->getMessage());
                sleep(1); // maybe down, or connection timeout, wait a little and try again
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function sendPairingResponse(PairingResponse $response)
    {
        $responseListKey = sprintf(self::PAIRING_RESPONSE_LIST_KEY_TEMPLATE, $response->request->case->id);

        $data = [
            'sealedHealthAuthorityPublicKey' =>
                base64_encode($response->sealedHealthAuthorityPublicKey)
        ];

        try {
            $this->client->rpush($responseListKey, [json_encode($data)]);
        } catch (Throwable $e) {
            throw new RuntimeException('Error sending pairing response', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function sendPairingException(PairingException $exception)
    {
        $responseListKey = sprintf(self::PAIRING_RESPONSE_LIST_KEY_TEMPLATE, $exception->getRequest()->case->id);

        $data = [
            'error' => $exception->getMessage()
        ];

        try {
            $this->client->rpush($responseListKey, [json_encode($data)]);
        } catch (Throwable $e) {
            throw new RuntimeException('Error sending pairing exception', 0, $e);
        }
    }
}
