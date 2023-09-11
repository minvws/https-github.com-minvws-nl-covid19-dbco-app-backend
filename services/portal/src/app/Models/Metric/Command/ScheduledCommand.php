<?php

declare(strict_types=1);

namespace App\Models\Metric\Command;

use App\Models\Metric\CounterMetric;

final class ScheduledCommand extends CounterMetric
{
    protected string $name = 'scheduled_command_counter';
    protected string $help = 'Counts the scheduled commands and the status';

    private function __construct(string $class, string $status)
    {
        $this->labels = [
            'class' => $class,
            'status' => $status,
        ];
    }

    public static function before(string $class): self
    {
        return new self($class, 'before');
    }

    public static function failure(string $class): self
    {
        return new self($class, 'failure');
    }

    public static function success(string $class): self
    {
        return new self($class, 'success');
    }
}
