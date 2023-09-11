<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Encryption\Security;

use Predis\Client as PredisClient;

class RedisSecurityCache implements SecurityCache
{
    private const NS_VALUE      = 'value';
    private const NS_SECRET_KEY = 'secretKey';

    private const REDIS_KEY_TEMPLATE = 'securityCache:%s:%s';

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
        return $this->client->exists($this->getRedisKey(self::NS_VALUE, $key)) === 1;
    }

    /**
     * @inheritDoc
     */
    public function getValue(string $key): string
    {
        $value = $this->client->get($this->getRedisKey(self::NS_VALUE, $key));
        if ($value !== null) {
            return $value;
        } else {
            throw new CacheEntryNotFoundException('Value not found for key "' . $key . '"');
        }
    }

    /**
     * @inheritDoc
     */
    public function setValue(string $key, string $value): void
    {
        $this->client->set($this->getRedisKey(self::NS_VALUE, $key), $value);
    }

    /**
     * @inheritDoc
     */
    public function deleteValue(string $key): bool
    {
        return $this->client->del($this->getRedisKey(self::NS_VALUE, $key)) === 1;
    }

    /**
     * @inheritdoc
     */
    public function hasSecretKey(string $identifier): bool
    {
        return $this->client->exists($this->getRedisKey(self::NS_SECRET_KEY, $identifier)) === 1;
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): string
    {
        $value = $this->client->get($this->getRedisKey(self::NS_SECRET_KEY, $identifier));
        if ($value !== null) {
            return base64_decode($value);
        } else {
            throw new CacheEntryNotFoundException('Secret key not found for identifier "' . $identifier . '"');
        }
    }

    /**
     * @inheritdoc
     */
    public function setSecretKey(string $identifier, string $secretKey): void
    {
        $this->client->set($this->getRedisKey(self::NS_SECRET_KEY, $identifier), base64_encode($secretKey));
    }

    /**
     * @inheritdoc
     */
    public function deleteSecretKey(string $identifier): bool
    {
        return $this->client->del($this->getRedisKey(self::NS_SECRET_KEY, $identifier)) === 1;
    }
}
