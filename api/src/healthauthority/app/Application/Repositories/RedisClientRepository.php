<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeInterface;
use DBCO\HealthAuthorityAPI\Application\Models\Client;
use DBCO\HealthAuthorityAPI\Application\Models\ClientCase;
use Predis\Client as PredisClient;
use Psr\Log\LoggerInterface;

/**
 * Used for registering / retrieving clients.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
class RedisClientRepository implements ClientRepository
{
    private const CLIENT_KEY_TEMPLATE = 'client:%s';
    private const CASE_CLIENTS_KEY_TEMPLATE = 'case:%s:clients';

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
    public function registerClient(Client $client, DateTimeInterface $expiresAt)
    {
        // store client
        $clientKey = sprintf(self::CLIENT_KEY_TEMPLATE, $client->token);

        $data = [
            'case' => [
                'uuid' => $client->case->uuid
            ],
            'clientPublicKey' => base64_encode($client->clientPublicKey),
            'healthAuthorityPublicKey' => base64_encode($client->healthAuthorityPublicKey),
            'healthAuthoritySecretKey' => base64_encode($client->healthAuthoritySecretKey),
            'sealedHealthAuthorityPublicKey' => base64_encode($client->sealedHealthAuthorityPublicKey),
            'receiveKey' => base64_encode($client->receiveKey),
            'transmitKey' => base64_encode($client->transmitKey)
        ];

        // TODO: proper expiry
        $expires = $expiresAt->getTimestamp() - time();
        $this->client->setex($clientKey, $expires, json_encode($data));

        // store client for case
        $caseClientsKey = sprintf(self::CASE_CLIENTS_KEY_TEMPLATE, $client->case->uuid);
        $this->client->rpush($caseClientsKey, [$client->token]);
        $this->client->expire($caseClientsKey, $expires);
    }

    /**
     * @inheritDoc
     */
    public function getClient(string $token): ?Client
    {
        $key = sprintf(self::CLIENT_KEY_TEMPLATE, $token);

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
            base64_decode($data->clientPublicKey),
            base64_decode($data->healthAuthorityPublicKey),
            base64_decode($data->healthAuthoritySecretKey),
            base64_decode($data->sealedHealthAuthorityPublicKey),
            base64_decode($data->receiveKey),
            base64_decode($data->transmitKey)
        );
    }


    /**
     * Returns the paired clients for the given case.
     *
     * @param string $caseUuid
     *
     * @return Client[]
     */
    public function getClientsForCase(string $caseUuid): array
    {
        $caseClientsKey = sprintf(self::CASE_CLIENTS_KEY_TEMPLATE, $caseUuid);
        $tokens = $this->client->lrange($caseClientsKey, 0, 100); // TODO: arbitrary max

        $clients = [];
        foreach ($tokens as $token) {
            $client = $this->getClient($token);;
            if ($client !== null) {
                $client[] = $client;
            }
        }

        return $clients;
    }
}
