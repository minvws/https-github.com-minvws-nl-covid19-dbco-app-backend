<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Services\Chores\ChoreService;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Webmozart\Assert\Assert;

readonly class SoftDeletedModelsService
{
    public function __construct(
        private ChoreService $choreService,
    ) {
    }

    public function purgeCasesWithChoresBeforeDate(DateTimeInterface $date): int
    {
        return DB::transaction(function () use ($date): int {
            $query = EloquentCase::onlyTrashed()
                ->where('deleted_at', '<=', $date->format('Y-m-d H:i:s'))
                ->limit(10_000);

            $this->choreService->forceDeleteByCaseUuids($query->pluck('uuid')->toArray());
            $deletedCasesCount = $query->forceDelete();

            Assert::integer($deletedCasesCount);
            return $deletedCasesCount;
        });
    }

    public function purgeTasksBeforeDate(DateTimeInterface $date): int
    {
        $deletedTasksCount = EloquentTask::onlyTrashed()
            ->where('deleted_at', '<=', $date->format('Y-m-d H:i:s'))
            ->forceDelete();

        Assert::integer($deletedTasksCount);
        return $deletedTasksCount;
    }
}
