<?php

declare(strict_types=1);

namespace App\Models\Metric\DataDisclosure;

use App\Models\Metric\CounterMetric;

final class StreamRequest extends CounterMetric
{
    protected string $name = 'data_disclosure_stream_requests';
    protected string $help = 'Counts the total number of requests for a specific exportable entity stream/index request';

    public function __construct(string $exportType, int $clientId, string $apiParameter)
    {
        $this->labels = [
            'export_type' => $exportType,
            'client_id' => (string) $clientId,
            'api_parameter' => $apiParameter,
        ];
    }
}
