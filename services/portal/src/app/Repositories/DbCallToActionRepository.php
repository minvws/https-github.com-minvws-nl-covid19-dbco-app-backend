<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CallToAction\ListOptions;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\EloquentOrganisation;
use App\Repositories\QueryBuilder\DbChoreResourceQueryBuilder;
use App\Services\AuthenticationService;
use App\Services\Chores\ChoreService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\ChoreResourceType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DbCallToActionRepository implements CallToActionRepository
{
    public const DAYS_VISIBLE_AFTER_EXPIRY = 14;

    public function __construct(
        public ChoreService $choreService,
        public AuthenticationService $authenticationService,
    ) {
    }

    public function listCallToActions(ListOptions $listOptions, EloquentOrganisation $organisation): LengthAwarePaginatorInterface
    {
        $myAssignedCallToActions = $this->getMyAssignedCallToActions($listOptions, $organisation);
        $unAssignedCallToActions = $this->getUnAssignedCallToActions($listOptions, $organisation);

        $mergedCollection = $myAssignedCallToActions->merge($unAssignedCallToActions);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $mergedCollection->forPage($currentPage, $listOptions->perPage)->values(),
            $mergedCollection->count(),
            $listOptions->perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()],
        );
    }

    public function getMyAssignedCallToActions(ListOptions $listOptions, EloquentOrganisation $organisation): Collection
    {
        $query = CallToAction::query();

        $query = $this->withChore($query)
            ->joinMyAssigned()
            ->whereOrganisation($organisation)
            ->whereActive()
            ->whereResourceTypeNotDeleted(ChoreResourceType::covidCase())
            ->orderByCallToActionListOptions($listOptions)
            ->toQuery();

        return $query->get();
    }

    public function getUnAssignedCallToActions(ListOptions $listOptions, EloquentOrganisation $organisation): Collection
    {
        $query = CallToAction::query();

        $query = $this->withChore($query)
            ->joinUnAssigned()
            ->whereOrganisation($organisation)
            ->whereActive()
            ->whereResourceTypeNotDeleted(ChoreResourceType::covidCase())
            ->orderByCallToActionListOptions($listOptions)
            ->toQuery();

        return $query->get();
    }

    /**
     * @throws Exception
     */
    public function getCallToAction(string $uuid, EloquentOrganisation $organisation): CallToAction
    {
        $query = CallToAction::query();

        $query = $this->withChore($query)
            ->whereOrganisation($organisation)
            ->whereResourceTypeNotDeleted(ChoreResourceType::covidCase())
            ->toQuery();

        /** @var CallToAction|null $callToAction */
        $callToAction = $query->find($uuid);

        if ($callToAction === null) {
            throw new NotFoundHttpException('Call to action not found');
        }

        return $callToAction;
    }

    /**
     * @throws AuthenticationException
     */
    public function createCallToAction(
        string $subject,
        string $description,
    ): CallToAction {
        $callToAction = new CallToAction();
        $callToAction->subject = $subject;
        $callToAction->description = $description;
        $callToAction->created_by = $this->authenticationService->getAuthenticatedUser()->uuid;
        $callToAction->save();

        return $callToAction;
    }

    public function withChore(Builder $query): DbChoreResourceQueryBuilder
    {
        return new DbChoreResourceQueryBuilder($this->authenticationService, $query);
    }
}
