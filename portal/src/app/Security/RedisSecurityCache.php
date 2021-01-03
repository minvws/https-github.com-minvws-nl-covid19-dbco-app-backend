<?php
declare(strict_types=1);

namespace App\Security;

use DBCO\HealthAuthorityAPI\Application\Security\CacheEntryNotFoundException;
use Illuminate\Redis\Connections\Connection;

/**
 * Security cache that retrieves its entries from Redis.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class RedisSecurityCache implements SecurityCache
{
    private const NS_VALUE      = 'value';
    private const NS_SECRET_KEY = 'secretKey';

    private const REDIS_KEY_TEMPLATE = 'securityCache:%s:%s';

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
     * @param string $namespace
     * @param string $identifier
     *
     * @return string
     */
    private function getRedisKey(string $namespace, string $identifier): string
    {
        return sprintf(self::REDIS_KEY_TEMPLATE, $namespace, $identifier);
    }

    /**
     * @inheritDoc
     */
    public function hasValue(string $key): bool
    {
        return $this->connection->get($this->getRedisKey(self::NS_VALUE, $key)) !== null;
    }

    /**
     * @inheritDoc
     */
    public function getValue(string $key): string
    {
        $value = $this->connection->get($this->getRedisKey(self::NS_VALUE, $key));
        if ($value !== null) {
            return $value;
        } else {
            throw new CacheEntryNotFoundException('Value not found for key "' . $key . '"');
        }
    }

    /**
     * @inheritdoc
     */
    public function hasSecretKey(string $identifier): bool
    {
        return $this->connection->get($this->getRedisKey(self::NS_SECRET_KEY, $identifier)) !== null;
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): string
    {
        $value = $this->connection->get($this->getRedisKey(self::NS_SECRET_KEY, $identifier));
        if ($value !== null) {
            return base64_decode($value);
        } else {
            throw new CacheEntryNotFoundException('Secret key not found for identifier "' . $identifier . '"');
        }
    }
}
