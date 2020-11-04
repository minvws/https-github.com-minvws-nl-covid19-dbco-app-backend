<?php
namespace DBCO\PrivateAPI\Application\Repositories;

use DateTime;
use DateTimeInterface;
use Predis\Client as PredisClient;

/**
 * Used for storing case data for later retrieval by clients.
 *
 * @package DBCO\PrivateAPI\Application\Repositories
 */
class RedisCaseRepository implements CaseRepository
{
    /**
     * @var PredisClient
     */
    private PredisClient $client;

    /**
     * Constructor.
     *
     * @param PredisClient $client
     */
    public function __construct(PredisClient $client)
    {
        $this->client = $client;
    }

    /**
     * Returns the Redis key for the given case token.
     *
     * @param string $token
     *
     * @return string
     */
    private function getRedisKeyForToken(string $token): string
    {
        return 'case:' . $token;
    }

    /**
     * @inheritDoc
     */
    public function storeCase(string $token, string $payload, DateTimeInterface $expiresAt)
    {
        $key = $this->getRedisKeyForToken($token);
        $expiresInSeconds = $expiresAt->getTimestamp() - time();
        $data = [
            'expiresAt' => $expiresAt->format(DateTime::ATOM),
            'payload' => $payload
        ];

        $this->client->setex($key, $expiresInSeconds, json_encode($data));
    }
}
