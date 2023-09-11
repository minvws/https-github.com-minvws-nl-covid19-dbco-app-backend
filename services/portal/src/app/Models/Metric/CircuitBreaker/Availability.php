<?php

declare(strict_types=1);

namespace App\Models\Metric\CircuitBreaker;

use App\Models\Metric\GaugeMetric;

use function sprintf;

final class Availability extends GaugeMetric
{
    private string $service;

    private function __construct(string $service, float $value)
    {
        $this->service = $service;
        $this->value = $value;
    }

    public function getName(): string
    {
        return sprintf('%s_circuit_breaker_gauge', $this->service);
    }

    public function getHelp(): string
    {
        return sprintf('Gauge for the circuit breaker used for: "%s"', $this->service);
    }

    public static function available(string $service): self
    {
        return new self($service, 0.0);
    }

    public static function notAvailable(string $service): self
    {
        return new self($service, 1.0);
    }
}
