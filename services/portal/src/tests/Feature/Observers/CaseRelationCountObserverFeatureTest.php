<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\TestResult;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\Feature\FeatureTestCase;

use function config;
use function get_class;

class CaseRelationCountObserverFeatureTest extends FeatureTestCase
{
    public function testCreatedLogsErrorIfTestResultCountExceedsThreshold(): void
    {
        $configKey = TestResult::class;
        $threshold = $this->faker->numberBetween(1, 5);
        config()->set("relationcounts.log_threshold.$configKey", $threshold);
        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->createQuietly();
        TestResult::factory()
            ->for($case, 'covidCase')
            ->count($threshold)
            ->create();

        $current = $threshold + 1;
        Log::expects('error')
            ->with(
                "Number of related records of type $configKey for case $case->uuid exceeds limit of $threshold; current count is $current",
            );

        TestResult::factory()
            ->for($case, 'covidCase')
            ->create();
    }

    public function testCreatedLogsErrorIfTaskCountExceedsThreshold(): void
    {
        Event::fake([
            JobProcessed::class,
        ]);

        $threshold = $this->faker->numberBetween(1, 5);
        config()->set('relationcounts.log_threshold.' . EloquentTask::class, $threshold);
        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->createQuietly();
        EloquentTask::factory()
            ->for($case, 'covidCase')
            ->count($threshold)
            ->create();

        $configKey = get_class(EloquentTask::getSchema()->getCurrentVersion()->newInstance());
        $current = $threshold + 1;
        Log::expects('error')
            ->with(
                "Number of related records of type $configKey for case $case->uuid exceeds limit of $threshold; current count is $current",
            );

        EloquentTask::factory()
            ->for($case, 'covidCase')
            ->create();
    }
}
