<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DBCO\HealthAuthorityAPI\Application\Models\Client;
use Predis\Client as PredisClient;
use Psr\Log\LoggerInterface;

/**
 * Used for registering / retrieving clients.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
class RedisClientRepository implements ClientRepository
{
    private const KEY_CLIENT_TEMPLATE = 'client:%s';

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
    public function registerClient(Client $client)
    {
        $key = sprintf(self::KEY_CLIENT_TEMPLATE, $client->token);

        $data = [
            'case' => [
                'uuid' => $client->case->uuid
            ],
            'clientPublicKey' => base64_encode($client->clientPublicKey),
            'healthAuthorityPublicKey' => base64_encode($client->healthAuthorityPublicKey),
            'healthAuthoritySecretKey' => base64_encode($client->healthAuthoritySecretKey),
            'receiveKey' => base64_encode($client->receiveKey),
            'transmitKey' => base64_encode($client->transmitKey)
        ];

        $this->client->setex($key, 3600, json_encode($data));
    }

    /**
     * @inheritDoc
     */
    public function getClient(string $token): ?Client
    {
        $key = sprintf(self::KEY_CLIENT_TEMPLATE, $token);

        $data = $this->client->get($key);
        if ($data === null) {
            return null;
        }

        $data = json_decode($data);
        if (!$data) {
            return null;
        }

        return new Client(
            $token,
            new ClientCase($data->case->uuid),
            $data->clientPublicKey,
            $data->healthAuthorityPublicKey,
            $data->healthAuthoritySecretKey,
            $data->receiveKey,
            $data->transmitKey
        );
    }
}
