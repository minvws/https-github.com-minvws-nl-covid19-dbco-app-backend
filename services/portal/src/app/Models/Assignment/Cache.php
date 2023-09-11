<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use Closure;

use function array_key_exists;

class Cache
{
    private array $cache = [];

    public function reset(): void
    {
        $this->cache = [];
    }

    private function key(string $class, string $key): string
    {
        return $class . '#' . $key;
    }

    public function get(string $class, string $key, ?Closure $setter = null): mixed
    {
        if (!$this->exists($class, $key) && $setter !== null) {
            $this->set($class, $key, $setter());
        }

        return $this->cache[$this->key($class, $key)] ?? null;
    }

    public function set(string $class, string $key, mixed $value): void
    {
        $this->cache[$this->key($class, $key)] = $value;
    }

    private function exists(string $class, string $key): bool
    {
        return array_key_exists($this->key($class, $key), $this->cache);
    }
}
