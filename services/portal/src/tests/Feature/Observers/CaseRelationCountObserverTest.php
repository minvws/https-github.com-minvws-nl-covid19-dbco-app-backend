<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Exceptions\RelationCountsThresholdNotFoundException;
use App\Models\Eloquent\CountableCaseRelation;
use App\Models\Eloquent\TestResult;
use App\Observers\CaseRelationCountObserver;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\Feature\FeatureTestCase;

use function config;
use function sprintf;

class CaseRelationCountObserverTest extends FeatureTestCase
{
    private CaseRelationCountObserver $caseRelationCountObserver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseRelationCountObserver = new CaseRelationCountObserver();
    }

    /**
     * @throws RelationCountsThresholdNotFoundException
     */
    public function testCreatedIsIdleWhenCountBelowThreshold(): void
    {
        $model = Mockery::mock(TestResult::class);
        $threshold = $this->configureThreshold($model);

        $model->expects('getCaseRelationCount')->andReturn($threshold - 1);
        $model->expects('getConfigKey')->andReturn($model::class);
        $model->expects('getCaseUuid')->never();
        Log::expects('error')->never();

        $this->caseRelationCountObserver->created($model);
    }

    /**
     * @throws RelationCountsThresholdNotFoundException
     */
    public function testCreatedIsIdleWhenCountEqualsThreshold(): void
    {
        $model = Mockery::mock(TestResult::class);
        $threshold = $this->configureThreshold($model);

        $model->expects('getCaseRelationCount')->andReturn($threshold);
        $model->expects('getConfigKey')->andReturn($model::class);
        $model->expects('getCaseUuid')->never();
        Log::expects('error')->never();

        $this->caseRelationCountObserver->created($model);
    }

    /**
     * @throws RelationCountsThresholdNotFoundException
     */
    public function testCreatedLogsErrorWhenCountExceedsThreshold(): void
    {
        $model = Mockery::mock(TestResult::class);
        $threshold = $this->configureThreshold($model);
        $exceed = $threshold + 1;
        $configKey = $model::class;
        $uuid = $this->faker->uuid();

        $model->expects('getCaseRelationCount')->andReturn($exceed);
        $model->expects('getConfigKey')->andReturn($configKey);
        $model->expects('getCaseUuid')->andReturn($uuid);
        Log::expects('error')
            ->with(
                sprintf(
                    "Number of related records of type %s for case %s exceeds limit of %d; current count is %d",
                    $configKey,
                    $uuid,
                    $threshold,
                    $exceed,
                ),
            );

        $this->caseRelationCountObserver->created($model);
    }

    public function testCreatedThrowsThresholdNotFoundException(): void
    {
        $model = Mockery::mock(TestResult::class);

        $model->expects('getCaseRelationCount');
        $model->expects('getConfigKey')->twice()->andReturn($model::class);
        $this->expectException(RelationCountsThresholdNotFoundException::class);

        $this->caseRelationCountObserver->created($model);
    }

    private function configureThreshold(CountableCaseRelation $model): int
    {
        $threshold = $this->faker->numberBetween(5, 50_000);
        config()->set('relationcounts.log_threshold.' . $model::class, $threshold);

        return $threshold;
    }
}
