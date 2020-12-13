<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

use Predis\Client as PredisClient;

/**
 * Security cache that stores its entries in Redis.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class RedisSecurityCache implements SecurityCache
{
    private const REDIS_KEY_TEMPLATE = 'secretKey:%s';

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
     * Get Redis key name.
     *
     * @param string $identifier
     *
     * @return string
     */
    private function getRedisKey(string $identifier): string
    {
        return sprintf(self::REDIS_KEY_TEMPLATE, $identifier);
    }

    /**
     * @inheritdoc
     */
    public function hasSecretKey(string $identifier): bool
    {
        return $this->client->exists($this->getRedisKey($identifier)) === 1;
    }

    /**
     * Get secret key for the given identifier.
     *
     * @param string $identifier
     *
     * @return string|null
     */
    public function getSecretKey(string $identifier): ?string
    {
        $key = $this->client->get($this->getRedisKey($identifier));
        return $key !== null ? base64_decode($key) : null;
    }

    /**
     * Store secret key with the given identifier.
     *
     * @param string $identifier
     * @param string $secretKey
     *
     * @return void
     */
    public function setSecretKey(string $identifier, string $secretKey): void
    {
        $this->client->set($this->getRedisKey($identifier), base64_encode($secretKey));
    }

    /**
     * Delete secret key with the given identifier.
     *
     * @param string $identifier
     */
    public function deleteSecretKey(string $identifier): void
    {
        $this->client->del($this->getRedisKey($identifier));
    }
}
