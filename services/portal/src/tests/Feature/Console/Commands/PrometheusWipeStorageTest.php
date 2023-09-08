<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Exception;
use Mockery\MockInterface;
use Prometheus\CollectorRegistry;
use Tests\Feature\FeatureTestCase;

final class PrometheusWipeStorageTest extends FeatureTestCase
{
    public function testHandleSuccess(): void
    {
        $this->mock(PrometheusExporter::class, function (MockInterface $mock): void {
            $collectorRegistry = $this->createMock(CollectorRegistry::class);
            $collectorRegistry->expects($this->once())->method('wipeStorage');

            $mock->expects('getPrometheus')->andReturns($collectorRegistry);
        });

        $this->artisan('prometheus:wipe-storage')
            ->assertSuccessful()
            ->expectsOutput('Successfully wiped prometheus metrics from storage')
            ->execute();
    }

    public function testHandleFailure(): void
    {
        $this->mock(PrometheusExporter::class, function (MockInterface $mock): void {
            $collectorRegistry = $this->createMock(CollectorRegistry::class);
            $collectorRegistry->expects($this->once())->method('wipeStorage')
                ->willThrowException(new Exception('wipeError'));

            $mock->expects('getPrometheus')->andReturns($collectorRegistry);
        });

        $this->artisan('prometheus:wipe-storage')
            ->assertFailed()
            ->expectsOutput('Failed to wipe prometheus metrics from storage; wipeError')
            ->execute();
    }
}
