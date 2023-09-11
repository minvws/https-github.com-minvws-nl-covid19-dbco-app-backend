<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners\Audit;

use App\Listeners\Audit\MeasureAuditEventFailure;
use Illuminate\Support\Facades\Event;
use MinVWS\Audit\Events\AuditEventSchemaDeviates;
use MinVWS\Audit\Events\AuditEventSchemaMissing;
use MinVWS\Audit\Events\AuditEventSpecDeviates;
use MinVWS\Audit\Events\AuditEventSpecMissing;
use Tests\Feature\FeatureTestCase;

final class MeasureAuditEventFailureTest extends FeatureTestCase
{
    public function testEventSubscription(): void
    {
        Event::fake();

        Event::assertListening(
            AuditEventSchemaMissing::class,
            [MeasureAuditEventFailure::class, 'whenSchemaIsMissing'],
        );
        Event::assertListening(
            AuditEventSchemaDeviates::class,
            [MeasureAuditEventFailure::class, 'whenSchemaDeviates'],
        );
        Event::assertListening(
            AuditEventSpecMissing::class,
            [MeasureAuditEventFailure::class, 'whenSpecIsMissing'],
        );
        Event::assertListening(
            AuditEventSpecDeviates::class,
            [MeasureAuditEventFailure::class, 'whenSpecDeviates'],
        );
    }
}
