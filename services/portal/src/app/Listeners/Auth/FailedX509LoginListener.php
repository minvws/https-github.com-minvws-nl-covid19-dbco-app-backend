<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Models\Metric\DataDisclosure\InvalidCertificate;
use App\Services\MetricService;

class FailedX509LoginListener
{
    public function __construct(
        private readonly MetricService $metricService,
    ) {
    }

    public function handle(): void
    {
        $this->metricService->measure(new InvalidCertificate());
    }
}
