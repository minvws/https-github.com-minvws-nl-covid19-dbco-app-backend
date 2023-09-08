<?php

declare(strict_types=1);

namespace App\Models\Metric\Test;

use App\Models\Metric\CounterMetric;

final class Test extends CounterMetric
{
    protected string $name = 'test';
    protected string $help = 'Test metric';

    public function __construct(string $message)
    {
        $this->labels = [
            'message' => $message,
        ];
    }

    public static function withMessage(string $message): self
    {
        return new self($message);
    }
}
