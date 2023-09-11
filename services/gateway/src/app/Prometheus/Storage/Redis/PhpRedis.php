<?php

namespace App\Prometheus\Storage\Redis;

use Prometheus\Exception\StorageException;
use Redis;

class PhpRedis implements Connection
{
    use HandleException;

    private Redis $redis;
    private array $options;

    private bool $connectionInitialized = false;

    private static array $defaultOptions = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.1,
        'read_timeout' => 10,
        'persistent_connections' => false,
        'password' => null,
    ];

    protected function __construct(array $options, ?Redis $redis = null)
    {
        $this->redis = $redis ?? new Redis();
        $this->options = $options;
    }

    public static function create(array $options): self
    {
        return new self(array_merge(self::$defaultOptions, $options));
    }

    /**
     * @throws StorageException
     */
    public static function forConnection(Redis $redis): self
    {
        if (!$redis->isConnected()) {
            throw new StorageException('Connection to Redis server not established');
        }

        $self = new self([], $redis);
        $self->connectionInitialized = true;
        return $self;
    }

    /**
     * @throws StorageException
     */
    private function ensureOpenConnection(): void
    {
        if ($this->connectionInitialized) {
            return;
        }

        $this->handleException(fn () => $this->connectToServer());

        if ($this->options['password'] !== null) {
            $this->redis->auth($this->options['password']);
        }

        if (isset($this->options['database'])) {
            $this->redis->select($this->options['database']);
        }

        $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $this->options['read_timeout']);

        $this->connectionInitialized = true;
    }

    /**
     * @throws StorageException
     */
    private function connectToServer(): void
    {
        if ($this->options['persistent_connections'] !== false) {
            $connectionSuccessful = $this->redis->pconnect(
                $this->options['host'],
                (int)$this->options['port'],
                (float)$this->options['timeout']
            );
        } else {
            $connectionSuccessful = $this->redis->connect($this->options['host'], (int)$this->options['port'], (float)$this->options['timeout']);
        }

        if (!$connectionSuccessful) {
            throw new StorageException("Can't connect to Redis server", 0);
        }
    }

    public function getPrefix(): ?string
    {
        $prefix = $this->redis->getOption(Redis::OPT_PREFIX);
        /** @phpstan-ignore-next-line */ // PHPStan thinks getOption returns an int
        return is_string($prefix) ? $prefix : null;
    }

    public function eval(string $script, array $args = [], int $numOfKeys = 0)
    {
        $this->ensureOpenConnection();
        return $this->handleException(fn() => $this->redis->eval($script, $args, $numOfKeys));
    }

    public function setNx(string $key, string $value): bool
    {
        $this->ensureOpenConnection();
        return $this->handleException(fn() => $this->redis->setNx($key, $value));
    }

    public function set(string $key, $value, $timeout = null): bool
    {
        $this->ensureOpenConnection();
        /** @phpstan-ignore-next-line */ // docblock is incorrect, null is allowed
        return $this->handleException(fn() => $this->redis->set($key, $value, $timeout));
    }

    public function sMembers(string $key): array
    {
        $this->ensureOpenConnection();
        return $this->handleException(fn() => $this->redis->sMembers($key));
    }

    public function hGetAll(string $key): array
    {
        $this->ensureOpenConnection();
        return $this->handleException(fn() => $this->redis->hGetAll($key));
    }

    public function keys(string $pattern): array
    {
        $this->ensureOpenConnection();
        return $this->handleException(fn() => $this->redis->keys($pattern));
    }

    public function get(string $key)
    {
        $this->ensureOpenConnection();
        return $this->handleException(fn() => $this->redis->get($key));
    }

    public function del(string ... $keyOrKeys): int
    {
        $this->ensureOpenConnection();
        return $this->handleException(fn() => $this->redis->del(...$keyOrKeys));
    }
}
