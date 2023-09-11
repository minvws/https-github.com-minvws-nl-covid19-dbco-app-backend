<?php

declare(strict_types=1);

namespace App\Models\Metric;

abstract class GaugeMetric implements Metric
{
    protected string $help;
    protected string $name;
    protected float $value;

    /** @var array<string, int|string> $labels */
    protected array $labels = [];

    public function getHelp(): string
    {
        return $this->help;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
