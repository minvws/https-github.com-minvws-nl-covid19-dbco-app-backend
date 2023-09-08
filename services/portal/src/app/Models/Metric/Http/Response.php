<?php

declare(strict_types=1);

namespace App\Models\Metric\Http;

use App\Models\Metric\CounterMetric;

final class Response extends CounterMetric
{
    protected string $name = 'response_status_counter';
    protected string $help = 'Counts the response status codes';

    public function __construct(string $method, string $uri, int $statusCode)
    {
        $this->labels = [
            'method' => $method,
            'uri' => $uri,
            'status' => (string) $statusCode,
        ];
    }
}
