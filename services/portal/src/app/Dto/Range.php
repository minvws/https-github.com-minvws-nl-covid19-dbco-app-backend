<?php

declare(strict_types=1);

namespace App\Dto;

class Range
{
    public function __construct(
        private readonly int $min,
        private readonly int $max,
    ) {
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }
}
