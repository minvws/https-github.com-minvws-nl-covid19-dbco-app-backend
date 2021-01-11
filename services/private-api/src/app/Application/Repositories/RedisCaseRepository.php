<?php
namespace DBCO\PrivateAPI\Application\Repositories;

use DateTime;
use DateTimeInterface;
use DBCO\Shared\Application\Models\SealedData;
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
    public function storeCase(string $token, SealedData $sealedCase, DateTimeInterface $expiresAt)
    {
        $key = $this->getRedisKeyForToken($token);
        $expiresInSeconds = $expiresAt->getTimestamp() - time();
        $data = [
            'sealedCase' => [
                'ciphertext' => base64_encode($sealedCase->ciphertext),
                'nonce' => base64_encode($sealedCase->nonce)
            ]
        ];

        $this->client->setex($key, $expiresInSeconds, json_encode($data));
    }
}
