<?php

declare(strict_types=1);

namespace App\Schema;

use BackedEnum;

use function array_combine;
use function array_map;
use function array_values;

class Enum
{
    private readonly array $cases;

    /**
     * @param array<EnumCase|BackedEnum> $cases
     */
    protected function __construct(array $cases)
    {
        $this->cases = array_combine(array_map(static fn ($c) => $c->value, $cases), $cases);
    }

    /**
     * @param array<EnumCase> $cases
     */
    public static function forCases(array $cases): self
    {
        return new self($cases);
    }

    /**
     * @param class-string<BackedEnum> $class
     */
    public static function forBackedEnum(string $class): self
    {
        return new self($class::cases());
    }

    public function cases(): array
    {
        return array_values($this->cases);
    }

    public function from(int|string $value): EnumCase|BackedEnum
    {
        return $this->cases[$value];
    }

    public function tryFrom(int|string $value): EnumCase|BackedEnum|null
    {
        return $this->cases[$value] ?? null;
    }
}
