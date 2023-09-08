<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\RelationCountsThresholdNotFoundException;
use App\Helpers\Config;
use App\Models\Eloquent\CountableCaseRelation;
use Illuminate\Support\Facades\Log;
use Webmozart\Assert\InvalidArgumentException;

use function sprintf;

class CaseRelationCountObserver
{
    /**
     * @throws RelationCountsThresholdNotFoundException
     */
    public function created(CountableCaseRelation $model): void
    {
        $caseRelationCount = $model->getCaseRelationCount();
        $threshold = $this->getThresholdForModel($model);

        if ($caseRelationCount <= $threshold) {
            return;
        }

        Log::error(sprintf(
            'Number of related records of type %s for case %s exceeds limit of %d; current count is %d',
            $model::class,
            $model->getCaseUuid(),
            $threshold,
            $caseRelationCount,
        ));
    }

    /**
     * @throws RelationCountsThresholdNotFoundException
     */
    private function getThresholdForModel(CountableCaseRelation $model): int
    {
        try {
            return Config::integer('relationcounts.log_threshold.' . $model->getConfigKey());
        } catch (InvalidArgumentException) {
            throw new RelationCountsThresholdNotFoundException($model->getConfigKey());
        }
    }
}
