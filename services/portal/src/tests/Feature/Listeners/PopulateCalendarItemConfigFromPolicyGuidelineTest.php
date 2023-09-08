<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Events\PolicyGuidelineCreated;
use App\Listeners\PopulateCalendarItemConfigFromPolicyGuideline;
use App\Models\Policy\PolicyGuideline;
use App\Services\CalendarItemConfigService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
final class PopulateCalendarItemConfigFromPolicyGuidelineTest extends FeatureTestCase
{
    public function testItListensToTheConfiguredEvents(): void
    {
        Event::fake();
        Event::assertListening(PolicyGuidelineCreated::class, PopulateCalendarItemConfigFromPolicyGuideline::class);
    }

    public function testCreatingPolicyGuidelineEmitsPolicyGuidelineCreatedEvent(): void
    {
        Event::fake();

        PolicyGuideline::factory()->create();

        Event::assertDispatched(PolicyGuidelineCreated::class);
    }

    public function testHandle(): void
    {
        $policyGuideline = PolicyGuideline::factory()->make();

        /** @var CalendarItemConfigService&MockInterface $calendarItemConfigService */
        $calendarItemConfigService = Mockery::mock(CalendarItemConfigService::class);
        $calendarItemConfigService
            ->shouldReceive('createDefaultCalendarItemConfigsForNewPolicyGuideline')
            ->once()
            ->with($policyGuideline)
            ->andReturnNull();

        /** @var PopulateCalendarItemConfigFromPolicyGuideline $listener */
        $listener = $this->app->make(PopulateCalendarItemConfigFromPolicyGuideline::class, [
            'calendarItemConfigService' => $calendarItemConfigService,
        ]);

        $listener->handle(new PolicyGuidelineCreated($policyGuideline));
    }
}
