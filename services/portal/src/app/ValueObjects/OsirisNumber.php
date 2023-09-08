<?php

declare(strict_types=1);

namespace App\ValueObjects;

final class OsirisNumber
{
    public function __construct(
        private readonly int $value,
    ) {
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }
}
