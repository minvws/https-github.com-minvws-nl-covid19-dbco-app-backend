<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Events\PolicyVersionCreated;
use App\Listeners\PopulatePolicyVersion;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\CalendarItemPopulator;
use App\Repositories\Policy\CalendarViewPopulator;
use App\Repositories\Policy\PolicyGuidelinePopulator;
use App\Repositories\Policy\RiskProfilePopulator;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
final class PopulatePolicyVersionTest extends FeatureTestCase
{
    public function testItListensToTheConfiguredEvents(): void
    {
        Event::fake();
        Event::assertListening(PolicyVersionCreated::class, PopulatePolicyVersion::class);
    }

    public function testCreatingPolicyVersionEmitsPolicyVersionCreatedEvent(): void
    {
        Event::fake();

        PolicyVersion::factory()->create();

        Event::assertDispatched(PolicyVersionCreated::class);
    }

    public function testHandle(): void
    {
        Event::fake();

        $policyVersion = PolicyVersion::factory()->create();

        /** @var PolicyGuidelinePopulator&MockInterface $policyVersionGuideline */
        $policyVersionGuideline = Mockery::mock(PolicyGuidelinePopulator::class);
        $policyVersionGuideline
            ->shouldReceive('populate')
            ->once()
            ->with($policyVersion);

        /** @var RiskProfilePopulator&MockInterface $riskProfilePopulator */
        $riskProfilePopulator = Mockery::mock(RiskProfilePopulator::class);
        $riskProfilePopulator
            ->shouldReceive('populate')
            ->once()
            ->with($policyVersion);

        /** @var CalendarItemPopulator&MockInterface $calendarItemPopulator */
        $calendarItemPopulator = Mockery::mock(CalendarItemPopulator::class);
        $calendarItemPopulator
            ->shouldReceive('populate')
            ->once()
            ->with($policyVersion);

        /** @var CalendarViewPopulator&MockInterface $calendarViewPopulator */
        $calendarViewPopulator = Mockery::mock(CalendarViewPopulator::class);
        $calendarViewPopulator
            ->shouldReceive('populate')
            ->once()
            ->with($policyVersion);

        /** @var Connection&MockInterface $db */
        $db = Mockery::mock(Connection::class);
        $db
            ->shouldReceive('transaction')
            ->once()
            ->with(
                Mockery::on(static function (Closure $callback) {
                    $callback();

                    return true;
                }),
            )
            ->andReturnNull();

        $listener = new PopulatePolicyVersion(
            $db,
            $policyVersionGuideline,
            $riskProfilePopulator,
            $calendarItemPopulator,
            $calendarViewPopulator,
        );

        $listener->handle(new PolicyVersionCreated($policyVersion));
    }
}
