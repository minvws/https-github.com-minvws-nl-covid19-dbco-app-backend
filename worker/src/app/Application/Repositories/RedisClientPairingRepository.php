<?php
namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Models\PairingRequest;
use DBCO\Worker\Application\Models\PairingRequestCase;
use DBCO\Worker\Application\Models\PairingResponse;
use Exception;
use Predis\Client as PredisClient;
use Psr\Log\LoggerInterface;

/**
 * Pairing gateway for the client.
 *
 * @package App\Application\Repositories
 */
class RedisClientPairingRepository implements ClientPairingRepository
{
    private const PAIRING_LIST_KEY = 'pairings';
    private const PAIRING_RESPONSE_LIST_KEY_TEMPLATE = 'pairing-response:%s';
    private const PAIRING_LIST_TIMEOUT = 60;

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
    public function waitForPairingRequest(): PairingRequest
    {
        while (true) {
            $result = $this->client->blpop(self::PAIRING_LIST_KEY, self::PAIRING_LIST_TIMEOUT);
            if (!is_array($result) || count($result) !== 2) {
                continue;
            }

            $data = json_decode($result[1]);
            $case = new PairingRequestCase($data->case->id);
            $sealedClientPublicKey = $data->sealedClientPublicKey;
            return new PairingRequest($case, $sealedClientPublicKey);
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
            $this->client->rpush($responseListKey, json_encode($data));
        } catch (Exception $e) {
            throw new Exception('Error sending pairing response', 0, $e);
        }
    }
}
