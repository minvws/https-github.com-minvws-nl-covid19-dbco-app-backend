<?php

declare(strict_types=1);

namespace App\Repositories\Metric;

use App\Models\Metric\CounterMetric;
use App\Models\Metric\GaugeMetric;
use App\Models\Metric\HistogramMetric;
use Arquivei\LaravelPrometheusExporter\PrometheusExporter;

use function array_keys;
use function array_values;

final class PrometheusRepository implements MetricRepository
{
    public function __construct(
        private readonly PrometheusExporter $prometheusExporter,
    ) {
    }

    public function measureCounter(CounterMetric $metric): void
    {
        $this->prometheusExporter
            ->getOrRegisterCounter($metric->getName(), $metric->getHelp(), array_keys($metric->getLabels()))
            ->inc(array_values($metric->getLabels()));
    }

    public function measureGauge(GaugeMetric $metric): void
    {
        $this->prometheusExporter
            ->getOrRegisterGauge($metric->getName(), $metric->getHelp(), array_keys($metric->getLabels()))
            ->set($metric->getValue(), array_values($metric->getLabels()));
    }

    public function measureHistogram(HistogramMetric $metric): void
    {
        $this->prometheusExporter
            ->getOrRegisterHistogram(
                $metric->getName(),
                $metric->getHelp(),
                array_keys($metric->getLabels()),
                $metric->getBuckets(),
            )
            ->observe($metric->getValue(), $metric->getLabels());
    }
}
