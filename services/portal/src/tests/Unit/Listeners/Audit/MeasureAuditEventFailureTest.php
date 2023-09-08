<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Audit;

use App\Listeners\Audit\MeasureAuditEventFailure;
use App\Models\Metric\Audit\AuditEventSpecDeviation;
use App\Models\Metric\Audit\AuditEventSpecMissing;
use App\Services\MetricService;
use Exception;
use Illuminate\Support\Collection;
use MinVWS\Audit\Events\AuditEventSchemaDeviates;
use MinVWS\Audit\Events\AuditEventSpecDeviates;
use MinVWS\Audit\Models\AuditEvent;
use Mockery;
use Tests\Unit\UnitTestCase;

final class MeasureAuditEventFailureTest extends UnitTestCase
{
    public function testSchemaMissingNotMeasuredWhenDisabled(): void
    {
        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure')
            ->never();

        $listener = new MeasureAuditEventFailure(false, $this->faker->boolean(), $metricService);
        $listener->whenSchemaIsMissing();
    }

    public function testSchemaDeviatesNotMeasuredWhenDisabled(): void
    {
        $mockEvent = new AuditEventSchemaDeviates(
            Mockery::mock(AuditEvent::class),
            new Exception(),
        );

        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure')
            ->never();

        $listener = new MeasureAuditEventFailure(false, $this->faker->boolean(), $metricService);
        $listener->whenSchemaDeviates($mockEvent);
    }

    public function testSchemaMissingMeasuredWhenEnabled(): void
    {
        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure');

        $listener = new MeasureAuditEventFailure(true, $this->faker->boolean(), $metricService);
        $listener->whenSchemaIsMissing();
    }

    public function testSchemaDeviatesMeasuredWhenEnabled(): void
    {
        $mockAuditEvent = Mockery::mock(AuditEvent::class);
        $mockAuditEvent->expects('getCode')
            ->atLeast()->once()
            ->andReturn($this->faker->word());

        $mockEvent = new AuditEventSchemaDeviates(
            $mockAuditEvent,
            new Exception(),
        );

        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure');

        $listener = new MeasureAuditEventFailure(true, $this->faker->boolean(), $metricService);
        $listener->whenSchemaDeviates($mockEvent);
    }

    public function testSpecMissingNotMeasuredWhenDisabled(): void
    {
        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure')
            ->never();

        $listener = new MeasureAuditEventFailure($this->faker->boolean(), false, $metricService);
        $listener->whenSpecIsMissing();
    }

    public function testSpecDeviatesNotMeasuredWhenDisabled(): void
    {
        $mockEvent = new AuditEventSpecDeviates(Mockery::mock(AuditEvent::class), specDiff: new Collection());

        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure')
            ->never();

        $listener = new MeasureAuditEventFailure($this->faker->boolean(), false, $metricService);
        $listener->whenSpecDeviates($mockEvent);
    }

    public function testSpecMissingMeasuredWhenEnabled(): void
    {
        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure')
            ->with(AuditEventSpecMissing::class);

        $listener = new MeasureAuditEventFailure($this->faker->boolean(), true, $metricService);
        $listener->whenSpecIsMissing();
    }

    public function testSpecDeviatesMeasuredWhenEnabled(): void
    {
        $mockAuditEvent = Mockery::mock(AuditEvent::class);
        $mockAuditEvent->expects('getCode')
            ->atLeast()->once()
            ->andReturn($this->faker->word());

        $mockEvent = new AuditEventSpecDeviates($mockAuditEvent, specDiff: new Collection());

        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure')
            ->with(AuditEventSpecDeviation::class);

        $listener = new MeasureAuditEventFailure($this->faker->boolean(), true, $metricService);
        $listener->whenSpecDeviates($mockEvent);
    }
}
