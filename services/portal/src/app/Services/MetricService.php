<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Metric\CounterMetric;
use App\Models\Metric\GaugeMetric;
use App\Models\Metric\HistogramMetric;
use App\Models\Metric\Metric;
use App\Repositories\Metric\MetricRepository;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

use function array_merge;

final class MetricService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MetricRepository $metricRepository,
    ) {
    }

    public function measure(Metric $metric): void
    {
        switch (true) {
            case $metric instanceof CounterMetric:
                $this->measureCounter($metric);
                break;
            case $metric instanceof GaugeMetric:
                $this->measureGauge($metric);
                break;
            case $metric instanceof HistogramMetric:
                $this->measureHistogram($metric);
                break;
            default:
                throw new UnexpectedValueException('unknown metric type');
        }
    }

    private function measureCounter(CounterMetric $metric): void
    {
        $this->logMetric($metric);
        $this->metricRepository->measureCounter($metric);
    }

    private function measureGauge(GaugeMetric $metric): void
    {
        $this->logMetric($metric, ['value' => $metric->getValue()]);
        $this->metricRepository->measureGauge($metric);
    }

    private function measureHistogram(HistogramMetric $metric): void
    {
        $this->logMetric($metric, [
            'buckets' => $metric->getBuckets(),
            'value' => $metric->getValue(),
        ]);
        $this->metricRepository->measureHistogram($metric);
    }

    private function logMetric(Metric $metric, array $context = []): void
    {
        $this->logger->debug('Measure metric', array_merge([
            'name' => $metric->getName(),
            'labels' => $metric->getLabels(),
        ], $context));
    }
}
