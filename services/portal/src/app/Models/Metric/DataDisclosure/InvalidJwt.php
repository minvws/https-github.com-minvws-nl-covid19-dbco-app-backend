<?php

declare(strict_types=1);

namespace App\Models\Metric\DataDisclosure;

use App\Models\Metric\CounterMetric;

final class InvalidJwt extends CounterMetric
{
    protected string $name = 'data_disclosure_invalid_jwt';
    protected string $help = 'Counts the total number of times an invalid JWT was used as cursor';

    public function __construct(int $clientId)
    {
        $this->labels = [
            'client_id' => (string) $clientId,
        ];
    }
}
