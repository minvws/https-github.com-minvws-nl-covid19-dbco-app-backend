<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CallToAction\ListOptions;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\EloquentOrganisation;
use App\Repositories\QueryBuilder\DbChoreResourceQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface CallToActionRepository
{
    public function listCallToActions(ListOptions $listOptions, EloquentOrganisation $organisation): LengthAwarePaginator;

    public function getCallToAction(string $uuid, EloquentOrganisation $organisation): CallToAction;

    public function createCallToAction(
        string $subject,
        string $description,
    ): CallToAction;

    public function withChore(Builder $query): DbChoreResourceQueryBuilder;
}
