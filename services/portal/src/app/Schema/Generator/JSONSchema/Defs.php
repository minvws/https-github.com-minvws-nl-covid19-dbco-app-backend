<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema;

use IteratorAggregate;
use Traversable;

use function ksort;

class Defs implements IteratorAggregate
{
    private array $defs = [];

    public function contains(string $name): bool
    {
        return isset($this->defs[$name]);
    }

    public function put(string $name, array $schema): void
    {
        $this->defs[$name] = $schema;
    }

    public function clear(): void
    {
        $this->defs = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->defs);
    }

    public function getIterator(): Traversable
    {
        $defs = $this->defs;
        ksort($defs);
        yield from $defs;
    }
}
