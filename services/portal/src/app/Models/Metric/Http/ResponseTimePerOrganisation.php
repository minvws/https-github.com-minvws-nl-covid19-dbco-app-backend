<?php

declare(strict_types=1);

namespace App\Models\Metric\Http;

use App\Models\Metric\HistogramMetric;

final class ResponseTimePerOrganisation extends HistogramMetric
{
    protected string $name = 'response_time_per_organisation_seconds';
    protected string $help = 'Observes response times per organisation';

    public function __construct(string $organisation, float $duration, ?array $buckets)
    {
        $this->buckets = $buckets;
        $this->labels = [
            'organisation' => $organisation,
        ];
        $this->value = $duration;
    }
}
