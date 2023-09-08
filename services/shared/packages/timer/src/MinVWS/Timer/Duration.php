<?php

declare(strict_types=1);

namespace MinVWS\Timer;

final class Duration
{
    private function __construct(private readonly float $nanoseconds)
    {
    }

    public static function fromSeconds(float $seconds): self
    {
        return new self($seconds * 1000000000);
    }

    public static function fromNanoseconds(float $nanoseconds): self
    {
        return new self($nanoseconds);
    }

    public function inSeconds(): float
    {
        return $this->nanoseconds / 1000000000;
    }
}
