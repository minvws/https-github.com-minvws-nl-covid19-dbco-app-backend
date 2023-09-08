<?php

declare(strict_types=1);

namespace App\Models\Metric\Mittens;

use App\Models\Metric\CounterMetric;

final class MittensRequest extends CounterMetric
{
    protected string $name = 'mittens_request';
    protected string $help = 'A request to mittens';

    private function __construct(string $uri, string $status)
    {
        $this->labels = [
            'uri' => $uri,
            'status' => $status,
        ];
    }

    public static function response(string $uri, int $responseCode): self
    {
        return new self($uri, (string) $responseCode);
    }

    public static function connectionError(string $uri): self
    {
        return new self($uri, 'connection_error');
    }

    public static function circuitBreakerIntercepted(string $uri): self
    {
        return new self($uri, 'circuit_breaker_intercepted');
    }
}
