<?php

declare(strict_types=1);

namespace App\Policies;

use App\Exceptions\ExpertQuestionNotFoundHttpException;
use App\Exceptions\ExpertQuestionUnavailableException;
use App\Helpers\CaseHelper;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\ExpertQuestion\ExpertQuestionTypeRoleMap;
use App\Services\AuthenticationService;
use App\Services\ExpertQuestion\ExpertQuestionService;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use MinVWS\DBCO\Enum\Models\Permission;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_map;
use function in_array;
use function is_null;
use function is_string;
use function request;

class ExpertQuestionPolicy
{
    private EloquentOrganisation $requiredSelectedOrganisation;
    private ExpertQuestionService $expertQuestionService;

    public function __construct(AuthenticationService $authenticationService, ExpertQuestionService $expertQuestionService)
    {
        $this->requiredSelectedOrganisation = $authenticationService->getRequiredSelectedOrganisation();
        $this->expertQuestionService = $expertQuestionService;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function list(EloquentUser $eloquentUser): bool
    {
        if (!$eloquentUser->can(Permission::expertQuestionList()->value)) {
            return false;
        }

        $requestedExpertQuestionType = request()->get('type');
        if (!is_string($requestedExpertQuestionType)) {
            return false;
        }

        return $this->canAccessRequestedExportQuestionType($eloquentUser, $requestedExpertQuestionType);
    }

    public function get(EloquentUser $eloquentUser, ExpertQuestion $expertQuestion): bool
    {
        if (!$eloquentUser->can(Permission::expertQuestionList()->value)) {
            return false;
        }

        return $this->isExpertQuestionAccessibleByUser($eloquentUser, $expertQuestion);
    }

    public function getWithoutBinding(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::expertQuestionList()->value);
    }

    public function assign(EloquentUser $eloquentUser, ExpertQuestion $expertQuestion): bool
    {
        if (!$eloquentUser->can(Permission::expertQuestionAssign()->value)) {
            return false;
        }

        return $this->isExpertQuestionAccessibleByUser($eloquentUser, $expertQuestion);
    }

    public function answer(EloquentUser $eloquentUser, ExpertQuestion $expertQuestion): bool
    {
        if (!$eloquentUser->can(Permission::expertQuestionAnswer()->value)) {
            return false;
        }

        return $this->isExpertQuestionAccessibleByUser($eloquentUser, $expertQuestion);
    }

    private function isExpertQuestionAccessibleByUser(EloquentUser $eloquentUser, ExpertQuestion $expertQuestion): bool
    {
        if (
            !is_null($expertQuestion->case)
            && !CaseHelper::isCaseAccessibleByOrganisation($expertQuestion->case, $this->requiredSelectedOrganisation)
        ) {
            return false;
        }

        if (!$this->expertQuestionService->canUserAccessExpertQuestion($eloquentUser, $expertQuestion)) {
            throw new ExpertQuestionNotFoundHttpException();
        }

        if ($expertQuestion->hasAssignment() && $expertQuestion->assigned_user_uuid !== $eloquentUser->uuid) {
            throw new ExpertQuestionUnavailableException();
        }

        return !$expertQuestion->hasAnswer();
    }

    private function canAccessRequestedExportQuestionType(
        EloquentUser $eloquentUser,
        string $requestedExpertQuestionType,
    ): bool {
        $expertQuestionTypes = array_map(
            static fn (ExpertQuestionType $expertQuestionType) => $expertQuestionType->value,
            ExpertQuestionTypeRoleMap::getExpertQuestionTypesForRoles($eloquentUser->getRolesArray()),
        );

        return in_array($requestedExpertQuestionType, $expertQuestionTypes, true);
    }
}
