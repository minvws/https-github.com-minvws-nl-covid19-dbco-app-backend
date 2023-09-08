<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Misc;

use App\Schema\Types\Type;

use function count;

class TypeDefs
{
    private array $typeDefs = [];

    /**
     * @param array<string, Type> $typeDefs
     */
    public function registerAll(array $typeDefs): void
    {
        foreach ($typeDefs as $name => $type) {
            $this->register($name, $type);
        }
    }

    public function register(string $name, Type $type): void
    {
        $this->typeDefs[$name] = $type;
    }

    public function get(string $name): ?Type
    {
        return $this->typeDefs[$name] ?? null;
    }

    public function count(): int
    {
        return count($this->typeDefs);
    }

    public function all(): array
    {
        return $this->typeDefs;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
