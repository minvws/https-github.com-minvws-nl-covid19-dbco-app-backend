<?php

declare(strict_types=1);

namespace App\Policies;

use App\Dto\Chore\Resource;
use App\Helpers\CaseHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\ExpertQuestion\ExpertQuestionTypeRoleMap;
use App\Models\OrganisationType;
use App\Repositories\ExpertQuestionRepository;
use App\Services\CaseLockService;
use App\Services\Chores\ChoreService;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use MinVWS\DBCO\Enum\Models\Permission;
use MinVWS\DBCO\Enum\Models\ResourcePermission;

use function app;

class EloquentCasePolicy
{
    public function __construct(
        private readonly ExpertQuestionRepository $expertQuestionRepository,
        private readonly ChoreService $choreService,
        private readonly CaseLockService $caseLockService,
    ) {
    }

    public function archive(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseArchive()->value);
    }

    public function addressLookup(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseAddressLookup()->value);
    }

    public function bsnLookup(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseBsnLookup()->value);
    }

    public function create(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseCreate()->value);
    }

    public function hardDelete(EloquentUser $eloquentUser, EloquentCase $case): bool
    {
        if (!$eloquentUser->can(Permission::caseComplianceDelete()->value)) {
            return false;
        }

        /** @var AccessRequestPolicy $accessRequestPolicy */
        $accessRequestPolicy = app(AccessRequestPolicy::class);
        return $accessRequestPolicy->viewAccessRequestCase($eloquentUser, $case);
    }

    public function view(EloquentUser $eloquentUser, ?EloquentCase $eloquentCase = null): bool
    {
        if ($this->edit($eloquentUser, $eloquentCase)) {
            return true;
        }

        if ($eloquentCase !== null && $this->viewSupervisionNationwide($eloquentUser, $eloquentCase)) {
            return true;
        }

        if ($eloquentCase === null) {
            return false;
        }

        return $this->viewSupervisionRegional($eloquentUser, $eloquentCase)
            || ($this->viewEloquentCaseByChoreResource($eloquentUser, $eloquentCase) || $this->editEloquentCaseByChoreResource(
                $eloquentUser,
                $eloquentCase,
            ));
    }

    public function edit(EloquentUser $eloquentUser, ?EloquentCase $eloquentCase = null): bool
    {
        if (!$eloquentUser->can(Permission::caseUserEdit()->value)) {
            return false;
        }

        if ($eloquentCase === null) {
            return true;
        }

        if ($eloquentUser->uuid === $eloquentCase->assigned_user_uuid) {
            return true;
        }

        return $this->editEloquentCaseByChoreResource($eloquentUser, $eloquentCase) && !$this->caseLockService->hasCaseLock(
            $eloquentCase,
            $eloquentUser,
        );
    }

    public function viewSupervisionNationwide(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$eloquentUser->can(Permission::caseViewSupervisionNationwide()->value)) {
            return false;
        }

        if (
            $eloquentCase->assignedOrganisation === null
            || $eloquentCase->assignedOrganisation->type === OrganisationType::regionalGGD()
        ) {
            return false;
        }

        return $this->expertQuestionRepository->hasQuestionForCaseAndTypesForExpertUser(
            $eloquentCase->uuid,
            $eloquentUser->uuid,
            [ExpertQuestionType::medicalSupervision()],
        );
    }

    public function viewSupervisionRegional(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$eloquentUser->can(Permission::caseViewSupervisionRegional()->value)) {
            return false;
        }

        $userOrganisationUuid = $eloquentUser->getRequiredOrganisation()->uuid;
        if ($eloquentCase->assigned_organisation_uuid !== null) {
            if ($eloquentCase->assigned_organisation_uuid !== $userOrganisationUuid) {
                return false;
            }
        } elseif ($eloquentCase->organisation_uuid !== $userOrganisationUuid) {
            return false;
        }

        $allowedQuestionTypes = ExpertQuestionTypeRoleMap::getExpertQuestionTypesForRoles($eloquentUser->getRolesArray());

        return $this->expertQuestionRepository->hasQuestionForCaseAndTypesForExpertUser(
            $eloquentCase->uuid,
            $eloquentUser->uuid,
            $allowedQuestionTypes,
        );
    }

    protected function viewEloquentCaseByChoreResource(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        $resource = new Resource($eloquentCase->getVersionedResourceType(), $eloquentCase->uuid);

        return $this->choreService->canAccessResource(ResourcePermission::view(), $resource, $eloquentUser);
    }

    protected function editEloquentCaseByChoreResource(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        $resource = new Resource($eloquentCase->getVersionedResourceType(), $eloquentCase->uuid);

        return $this->choreService->canAccessResource(ResourcePermission::edit(), $resource, $eloquentUser);
    }

    public function editContactStatus(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseEditContactStatus()->value);
    }

    public function approve(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseApprove()->value);
    }

    public function export(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseExport()->value);
    }

    public function list(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseListPlannerCases()->value);
    }

    public function listMine(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseListUserCases()->value);
    }

    public function listAccessRequests(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseListAccessRequests()->value);
    }

    public function viewPlannerTimeline(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$eloquentUser->can(Permission::caseViewPlannerTimeline()->value)) {
            return false;
        }

        return CaseHelper::isCaseAccessibleByOrganisation($eloquentCase, $eloquentUser->getRequiredOrganisation());
    }

    public function viewUserTimeline(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$eloquentUser->can(Permission::caseViewUserTimeline()->value)) {
            return false;
        }

        return $this->view($eloquentUser, $eloquentCase);
    }

    public function editBcoPhase(EloquentUser $eloquentUser, ?EloquentCase $eloquentCase = null): bool
    {
        if (!$eloquentUser->can(Permission::caseBcoPhaseEdit()->value)) {
            return false;
        }

        if ($eloquentCase === null && !$eloquentUser->can(Permission::casePlannerEdit()->value)) {
            return false;
        }

        if ($eloquentCase !== null && CaseHelper::isCaseAccessibleByOrganisation($eloquentCase, $eloquentUser->getRequiredOrganisation())) {
            if ($eloquentUser->uuid !== $eloquentCase->assigned_user_uuid && !$eloquentUser->can(Permission::casePlannerEdit()->value)) {
                return false;
            }
        }

        return true;
    }

    public function restore(EloquentUser $eloquentUser, EloquentCase $case): bool
    {
        if (!$eloquentUser->can(Permission::caseRestore()->value)) {
            return false;
        }

        return $this->viewAccessRequest($eloquentUser, $case);
    }

    public function search(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::caseSearch()->value);
    }

    /**
     * Only the organisation that created the case can find it in access requests
     */
    public function viewAccessRequest(EloquentUser $eloquentUser, EloquentCase $case): bool
    {
        /** @var AccessRequestPolicy $accessRequestPolicy */
        $accessRequestPolicy = app(AccessRequestPolicy::class);
        return $accessRequestPolicy->viewAccessRequestCase($eloquentUser, $case);
    }

    public function updateOrganisation(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$eloquentUser->can(Permission::casePlannerEdit()->value)) {
            return false;
        }

        return CaseHelper::isCaseAccessibleByOrganisation($eloquentCase, $eloquentUser->getRequiredOrganisation());
    }

    /**
     * Policies for a PlannerCase
     */

    public function softDelete(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$eloquentUser->can(Permission::casePlannerDelete()->value)) {
            return false;
        }

        if (!CaseHelper::isCaseAccessibleByOrganisation($eloquentCase, $eloquentUser->getRequiredOrganisation())) {
            return false;
        }

        return CaseHelper::isCaseUntouched($eloquentCase);
    }

    /**
     * A basicEdit-action should only allow a partial-edit of a case, e.g. contact information
     */
    public function basicEdit(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$this->canPlannerEditForOrganisation($eloquentUser, $eloquentCase)) {
            return false;
        }

        if (!CaseHelper::isCaseUntouched($eloquentCase) && !CaseHelper::isInEditWindow($eloquentCase)) {
            return false;
        }

        return $eloquentCase->assigned_user_uuid === null || $eloquentCase->assigned_user_uuid === $eloquentUser->uuid;
    }

    public function editMeta(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$this->canPlannerEditForOrganisation($eloquentUser, $eloquentCase)) {
            return false;
        }

        return $eloquentCase->bcoStatus !== BCOStatus::archived();
    }

    public function editAsUserOrAsPlanner(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if ($this->canPlannerEditForOrganisation($eloquentUser, $eloquentCase)) {
            return true;
        }

        return $this->edit($eloquentUser, $eloquentCase);
    }

    public function viewAsUserOrAsPlanner(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if ($this->canPlannerEditForOrganisation($eloquentUser, $eloquentCase)) {
            return true;
        }

        return $this->view($eloquentUser, $eloquentCase);
    }

    private function canPlannerEditForOrganisation(EloquentUser $eloquentUser, EloquentCase $eloquentCase): bool
    {
        if (!$eloquentUser->can(Permission::casePlannerEdit()->value)) {
            return false;
        }

        return CaseHelper::isCaseAccessibleByOrganisation($eloquentCase, $eloquentUser->getRequiredOrganisation());
    }

    public function createNote(EloquentUser $eloquentUser, EloquentCase $case): bool
    {
        if ($eloquentUser->can(Permission::casePlannerEdit()->value)) {
            return true;
        }

        return $eloquentUser->allowedCaseNotesByToken([$case->uuid]);
    }
}
