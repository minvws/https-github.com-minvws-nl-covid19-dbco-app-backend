<?php

declare(strict_types=1);

namespace App\Repositories\Metric;

use App\Models\Metric\CounterMetric;
use App\Models\Metric\GaugeMetric;
use App\Models\Metric\HistogramMetric;

interface MetricRepository
{
    public function measureCounter(CounterMetric $metric): void;

    public function measureGauge(GaugeMetric $metric): void;

    public function measureHistogram(HistogramMetric $metric): void;
}
