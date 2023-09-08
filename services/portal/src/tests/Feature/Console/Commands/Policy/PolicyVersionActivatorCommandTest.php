<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Policy;

use App\Models\Policy\PolicyVersion;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('policyVersion')]
class PolicyVersionActivatorCommandTest extends FeatureTestCase
{
    public function testActivatingPolicyVersion(): void
    {
        Carbon::setTestNow(Carbon::now()->startOfDay());

        $activePolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::now()->subDays(3),
        ]);
        $newPolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::now(),
        ]);

        $this->artisan('policy-version:activator:run')
            ->assertExitCode(0)
            ->execute();

        $this->assertEquals(PolicyVersionStatus::active(), $newPolicyVersion->refresh()->status);
        $this->assertEquals(PolicyVersionStatus::old(), $activePolicyVersion->refresh()->status);
    }

    public function testRunWithoutActivatablePolicyVersion(): void
    {
        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::now()->subDays(3),
        ]);

        $this->artisan('policy-version:activator:run')
            ->assertExitCode(0)
            ->execute();
    }
}
