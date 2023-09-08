<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Events\PolicyVersionCreated;
use App\Repositories\Policy\PolicyVersionPopulator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('policyVersion')]
final class PolicyVersionPopulatorTest extends FeatureTestCase
{
    private PolicyVersionPopulator $policyVersionPopulator;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->policyVersionPopulator = $this->app->make(PolicyVersionPopulator::class);
    }

    public function testPopulate(): void
    {
        $now = CarbonImmutable::now()->roundSeconds();

        CarbonImmutable::setTestNow($now);

        $policyVersion = $this->policyVersionPopulator->populate();

        $this->assertSame('Default', $policyVersion->name);
        $this->assertEquals($now, $policyVersion->start_date);
        $this->assertSame(PolicyVersionStatus::active(), $policyVersion->status);
    }
}
