<?php

declare(strict_types=1);

namespace App\Models\Metric;

abstract class HistogramMetric implements Metric
{
    protected string $help;
    protected string $name;

    /** @var array<int|string, float|int|string> $labels */
    protected array $labels = [];

    /** @var array<int|string, float>|null $labels */
    protected ?array $buckets = null;
    protected float $value;

    public function getBuckets(): ?array
    {
        return $this->buckets;
    }

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
