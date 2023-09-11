<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\CaseArchiveService;
use App\Services\CaseFragmentService;
use App\Services\CaseFragmentsValidationService;
use Illuminate\Support\ServiceProvider;

class CaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->when(CaseArchiveService::class)
            ->needs('$archiveStaleCompletedCasesInDays')
            ->giveConfig('misc.case.archiveStaleCompletedCasesInDays');

        $this->app->bind(CaseFragmentsValidationService::class, CaseFragmentService::class);
    }
}
