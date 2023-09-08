<?php

declare(strict_types=1);

namespace MinVWS\Timer;

final class Timer
{
    private function __construct(private float $start)
    {
    }

    public static function start(): Timer
    {
        $start = (float) hrtime(true);
        return new self($start);
    }

    public function stop(): Duration
    {
        try {
            $time = (float) hrtime(true) - $this->start;
            return Duration::fromNanoseconds($time);
        } finally {
            $this->reset();
        }
    }

    public static function wrap(callable $callable): Duration
    {
        $timer = self::start();
        $callable();
        return $timer->stop();
    }

    private function reset(): void
    {
        unset($this->start);
    }
}
