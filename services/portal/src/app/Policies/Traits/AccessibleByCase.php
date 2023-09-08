<?php

declare(strict_types=1);

namespace App\Policies\Traits;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Policies\EloquentCasePolicy;

use function app;

trait AccessibleByCase
{
    private function canEditCase(EloquentUser $eloquentUser, EloquentCase $case): bool
    {
        /** @var EloquentCasePolicy $casePolicy */
        $casePolicy = app(EloquentCasePolicy::class);
        return $casePolicy->edit($eloquentUser, $case);
    }

    private function canViewCase(EloquentUser $eloquentUser, EloquentCase $case): bool
    {
        /** @var EloquentCasePolicy $casePolicy */
        $casePolicy = app(EloquentCasePolicy::class);
        return $casePolicy->view($eloquentUser, $case);
    }
}
