<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

use function array_key_exists;
use function count;
use function in_array;

/**
 * @template TKey of string|int
 * @template TValue of Diff
 *
 * @property-read DiffType $diffType
 */
class DiffList implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array<TKey, TValue> */
    private array $items = [];

    /**
     * @param TKey $key
     *
     * @return TValue|null
     */
    public function get(string|int $key): ?Diff
    {
        return $this->items[$key] ?? null;
    }

    /**
     * @param TKey $key
     * @param TValue $diff
     */
    public function set(string|int $key, Diff $diff): void
    {
        $this->items[$key] = $diff;
    }

    /**
     * @param TKey $key
     */
    public function unset(string|int $key): void
    {
        unset($this->items[$key]);
    }

    /**
     * @param TKey $key
     */
    public function exists(string|int $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param TKey $key
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * @param TKey $key
     */
    public function offsetUnset(mixed $key): void
    {
        $this->unset($key);
    }

    /**
     * @param TKey $key
     */
    public function offsetExists(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function filter(DiffType ...$diffTypes): DiffList
    {
        $result = new self();

        foreach ($this->items as $key => $value) {
            if (in_array($value->diffType, $diffTypes, true)) {
                $result->set($key, $value);
            }
        }

        return $result;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
