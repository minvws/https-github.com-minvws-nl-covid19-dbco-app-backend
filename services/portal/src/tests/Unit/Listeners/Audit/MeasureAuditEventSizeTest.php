<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Audit;

use App\Listeners\Audit\MeasureAuditEventSize;
use App\Models\Metric\Audit\AuditEventLogSize;
use App\Services\MetricService;
use MinVWS\Audit\Events\AuditEventRegistered;
use MinVWS\Audit\Repositories\GgdSocAuditRepository;
use Mockery;
use Tests\Unit\UnitTestCase;

use function str_repeat;

final class MeasureAuditEventSizeTest extends UnitTestCase
{
    public function testAuditEventLogSizeIsMeasured(): void
    {
        $sizeInBytes = $this->faker->numberBetween(1, 1000);
        $encryptedAuditEvent = $this->faker->lexify(str_repeat('?', $sizeInBytes));
        $eventCode = $this->faker->word();

        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure')
            ->withArgs(function (AuditEventLogSize $arg) use ($eventCode, $sizeInBytes): bool {
                $this->assertArrayHasKey('event_code', $arg->getLabels());
                $this->assertEquals($eventCode, $arg->getLabels()['event_code']);
                $this->assertEquals($sizeInBytes, $arg->getValue());

                return true;
            });

        $listener = new MeasureAuditEventSize($metricService);
        ($listener)(new AuditEventRegistered($eventCode, $encryptedAuditEvent, GgdSocAuditRepository::class));
    }

    public function testAuditEventLogSizeIsIdle(): void
    {
        $sizeInBytes = $this->faker->numberBetween(1, 1000);
        $encryptedAuditEvent = $this->faker->lexify(str_repeat('?', $sizeInBytes));
        $eventCode = $this->faker->word();

        $metricService = Mockery::mock(MetricService::class);
        $metricService->expects('measure')
            ->never();

        $listener = new MeasureAuditEventSize($metricService);
        ($listener)(new AuditEventRegistered($eventCode, $encryptedAuditEvent, 'foobar'));
    }
}
