<?php

declare(strict_types=1);

namespace App\Models\Metric\DataDisclosure;

use App\Models\Metric\CounterMetric;

final class ExportRequests extends CounterMetric
{
    protected string $name = 'data_disclosure_export_requests';
    protected string $help = 'Counts the total number of export requests per entity type, client and duration';

    public function __construct(string $exportType, int $clientId)
    {
        $this->labels = [
            'export_type' => $exportType,
            'client_id' => (string) $clientId,
        ];
    }
}
