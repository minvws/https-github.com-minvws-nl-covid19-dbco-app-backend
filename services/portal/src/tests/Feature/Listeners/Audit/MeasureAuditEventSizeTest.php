<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners\Audit;

use App\Listeners\Audit\MeasureAuditEventSize;
use Illuminate\Support\Facades\Event;
use MinVWS\Audit\Events\AuditEventRegistered;
use Tests\Feature\FeatureTestCase;

final class MeasureAuditEventSizeTest extends FeatureTestCase
{
    public function testEventSubscription(): void
    {
        Event::fake();

        Event::assertListening(AuditEventRegistered::class, MeasureAuditEventSize::class);
    }
}
