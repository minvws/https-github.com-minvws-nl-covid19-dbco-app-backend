<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Events\PolicyGuidelineCreated;
use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\PolicyGuidelinePopulator;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('policyGuideline')]
final class PolicyGuidelinePopulatorTest extends FeatureTestCase
{
    private PolicyGuidelinePopulator $policyGuidelinePopulator;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class, PolicyGuidelineCreated::class]);

        $this->policyGuidelinePopulator = $this->app->make(PolicyGuidelinePopulator::class);
    }

    public function testPopulate(): void
    {
        $policyVersion = PolicyVersion::factory()->create();

        $result = $this->policyGuidelinePopulator->populate($policyVersion);

        $this->assertDatabaseCount(PolicyGuideline::class, 6);

        $this->assertEqualsCanonicalizing(
            [
                'symptomatic',
                'symptomatic_extended',
                'asymptomatic',
                'asymptomatic_extended',
                'quarantine',
                'no_quarantine',
            ],
            $result->pluck('identifier')->toArray(),
        );
    }

    public function testIdempotency(): void
    {
        $policyVersion = PolicyVersion::factory()->create();

        $this->policyGuidelinePopulator->populate($policyVersion);
        $this->assertDatabaseCount(PolicyGuideline::class, 6);

        $this->policyGuidelinePopulator->populate($policyVersion);
        $this->assertDatabaseCount(PolicyGuideline::class, 6);
    }
}
