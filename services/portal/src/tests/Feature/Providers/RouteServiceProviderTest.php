<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use App\Console\Commands\PurgeSoftDeletedModels;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;

use function sprintf;

class RouteServiceProviderTest extends FeatureTestCase
{
    public function testDeletedCaseIsAvailableWithinPurgeWindow(): void
    {
        $user = $this->createUser([], 'compliance');
        $case = $this->createCaseForUser($user, [
            'deleted_at' => CarbonImmutable::now()->subDays(PurgeSoftDeletedModels::PURGE_AFTER_DAYS),
        ]);

        $response = $this->makeRequest($user, $case);
        $response->assertOk();
    }

    public function testDeletedCaseIsNotAvailableOutsidePurgeWindow(): void
    {
        $user = $this->createUser([], 'compliance');
        $case = $this->createCaseForUser($user, [
            'deleted_at' => CarbonImmutable::now()->subDays(PurgeSoftDeletedModels::PURGE_AFTER_DAYS + 1),
        ]);

        $response = $this->makeRequest($user, $case);
        $response->assertNotFound();
    }

    public function testNonDeletedCaseIsAvailable(): void
    {
        $user = $this->createUser([], 'compliance');
        $case = $this->createCaseForUser($user, [
            'deleted_at' => null,
        ]);

        $response = $this->makeRequest($user, $case);
        $response->assertOk();
    }

    private function makeRequest(EloquentUser $user, EloquentCase $case): TestResponse
    {
        // The route picked makes use of the `softDeletedCase` binding. It is not special in any other way.
        return $this->be($user)
            ->post(sprintf('api/access-requests/case/%s/restore', $case->uuid));
    }
}
