<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\OsirisNotification;
use Illuminate\Support\Collection;

interface CaseOsirisNotificationRepository
{
    /**
     * @return Collection<int,EloquentCase>
     */
    public function getUpdatedCasesWithoutRecentOsirisNotification(): Collection;

    /**
     * @return Collection<int,EloquentCase>
     */
    public function findRetryableDeletedCases(): Collection;

    public function findLatestDeletedStatusNotification(EloquentCase $case): ?OsirisNotification;
}
