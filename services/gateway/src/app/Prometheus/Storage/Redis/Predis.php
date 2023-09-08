<?php

namespace App\Prometheus\Storage\Redis;

use Predis\Client;
use Predis\Command\Processor\KeyPrefixProcessor;

class Predis implements Connection
{
    use HandleException;

    private Client $redis;

    private static array $defaultOptions = [
        'parameters' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'persistent' => false,
            'timeout' => 0.1,
            'read_write_timeout' => 10
        ],
        'options' => []
    ];

    protected function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public static function create(array $options): self
    {
        $options = array_merge(self::$defaultOptions, $options);
        return new self(new Client($options['parameters'] ?? null, $options['options'] ?? null));
    }

    public static function forConnection(Client $client): self
    {
        return new self($client);
    }

    public function getPrefix(): ?string
    {
        $prefixProcessor = $this->redis->getOptions()->prefix;
        if ($prefixProcessor instanceof KeyPrefixProcessor && !empty($prefixProcessor->getPrefix())) {
            return $prefixProcessor->getPrefix();
        } else {
            return null;
        }
    }

    public function prefix(string $value): string
    {
        return $this->getPrefix() . $value;
    }

    public function eval(string $script, array $args = [], int $numOfKeys = 0)
    {
        return $this->handleException(fn () => $this->redis->eval($script, $numOfKeys, ...$args));
    }

    public function setNx(string $key, string $value): bool
    {
        return $this->handleException(fn () => $this->redis->setnx($key, $value) > 0);
    }

    /**
     * @param mixed $timeout
     */
    private function processTimeout($timeout): array
    {
        if (is_int($timeout)) {
            return ['EX', $timeout];
        }

        if (!is_array($timeout)) {
            return [];
        }

        $args = [];

        $timeoutTypes = ['EX', 'PX', 'EXAT', 'PXAT'];
        foreach ($timeoutTypes as $timeoutType) {
            if (isset($timeout[$timeoutType])) {
                $args[] = $timeoutType;
                $args[] = $timeout[$timeoutType];
                break;
            }
        }

        if (count($args) === 0 && in_array('KEEPTTL', $timeout)) {
            $args[] = 'KEEPTTL';
        }

        if (in_array('NX', $timeout)) {
            $args[] = 'NX';
        } elseif (in_array('XX', $timeout)) {
            $args[] = 'XX';
        }

        if (in_array('GET', $timeout)) {
            $args[] = 'GET';
        }

        return $args;
    }

    public function set(string $key, $value, $timeout = null): bool
    {
        $args = $this->processTimeout($timeout);

        return $this->handleException(function () use ($key, $value, $args) {
            $this->redis->set($key, $value, ...$args);

            return true;
        });
    }

    public function sMembers(string $key): array
    {
        return $this->handleException(fn () => $this->redis->smembers($key));
    }

    public function hGetAll(string $key): array
    {
        return $this->handleException(fn () => $this->redis->hgetall($key));
    }

    public function keys(string $pattern): array
    {
        return $this->handleException(fn () => $this->redis->keys($pattern));
    }

    public function get(string $key)
    {
        return $this->handleException(fn () => $this->redis->get($key));
    }

    public function del(string ... $keyOrKeys): int
    {
        return $this->handleException(fn () => $this->redis->del(...$keyOrKeys));
    }
}
