<?php
declare(strict_types=1);

namespace App\Security;

use Illuminate\Redis\Connections\Connection;

/**
 * Security cache that retrieves its entries from Redis.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class RedisSecurityCache implements SecurityCache
{
    private const REDIS_KEY_TEMPLATE = 'secretKey:%s';

    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
     * Get secret key for the given identifier.
     *
     * @param string $identifier
     *
     * @return string|null
     */
    public function getSecretKey(string $identifier): ?string
    {
        $key = $this->connection->get($this->getRedisKey($identifier));
        return $key !== null ? base64_decode($key) : null;
    }
}
