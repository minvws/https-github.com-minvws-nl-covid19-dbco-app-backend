<?php

declare(strict_types=1);

namespace App\Listeners\Api\Export;

use App\Events\Api\Export\InvalidJWTEncountered;
use App\Models\Metric\DataDisclosure\InvalidJwt;
use App\Services\MetricService;

class InvalidJwtEncounteredListener
{
    public function __construct(
        private readonly MetricService $metricService,
    ) {
    }

    public function handle(InvalidJWTEncountered $event): void
    {
        $this->metricService->measure(new InvalidJwt($event->client->id));
    }
}
