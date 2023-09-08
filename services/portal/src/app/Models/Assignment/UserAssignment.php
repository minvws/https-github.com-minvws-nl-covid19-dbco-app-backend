<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\Permission;

use function implode;

/**
 * Assignment that changes the assigned user.
 */
class UserAssignment implements Assignment
{
    private static ?array $statusToPermissionMapping = null;
    private EloquentUser $user;

    public function __construct(EloquentUser $user)
    {
        $this->user = $user;
    }

    public function getUser(): EloquentUser
    {
        return $this->user;
    }

    public function isValidForSelectedOrganisation(EloquentOrganisation $selectedOrganisation, bool $validateFull = true, ?Cache $cache = null): bool
    {
        if ($validateFull || $cache === null) {
            return $this->isValidForSelectedOrganisationUncached($selectedOrganisation, $validateFull);
        }

        $key = $this->user->roles ?? '';
        return $cache->get(
            self::class,
            $key,
            fn () => $this->isValidForSelectedOrganisationUncached($selectedOrganisation, $validateFull)
        );
    }

    private function isValidForSelectedOrganisationUncached(EloquentOrganisation $selectedOrganisation, bool $validateFull = false): bool
    {
        if (!$this->user->hasPermission('caseUserEdit')) {
            return false;
        }

        if ($validateFull && !$selectedOrganisation->isOrganisationForUser($this->user)) {
            return false;
        }

        return !$validateFull || $this->user->hasRecentlyLoggedIn();
    }

    private static function getRequiredPermissionForStatus(string $bcoStatus): string
    {
        if (self::$statusToPermissionMapping === null) {
            self::$statusToPermissionMapping = [
                BCOStatus::draft()->value => Permission::caseCanBeAssignedToDraft()->value,
                BCOStatus::open()->value => Permission::caseCanBeAssignedToOpen()->value,
                BCOStatus::completed()->value => Permission::caseCanBeAssignedToCompleted()->value,
                BCOStatus::archived()->value => Permission::caseCanBeAssignedToArchived()->value,
                BCOStatus::unknown()->value => Permission::caseCanBeAssignedToUnknown()->value,
            ];
        }

        return self::$statusToPermissionMapping[$bcoStatus] ?? Permission::caseCanBeAssignedToUnknown()->value;
    }

    public function isValidForCaseWithSelectedOrganisation(EloquentCase $case, EloquentOrganisation $selectedOrganisation, bool $validateFull = true, ?Cache $cache = null): bool
    {
        if ($validateFull || $cache === null) {
            return $this->isValidForCaseWithSelectedOrganisationUncached($case, $selectedOrganisation, $validateFull);
        }

        // every combination of the following fields has the same result, use it to cache the result
        $key = implode('#', [
            $case->getRawOriginal('bco_status'),
            $case->getRawOriginal('is_approved'),
            $case->getRawOriginal('organisation_uuid'),
            $case->getRawOriginal('assigned_organisation_uuid'),
            $this->user->roles,
        ]);

        return $cache->get(
            self::class,
            $key,
            fn () => $this->isValidForCaseWithSelectedOrganisationUncached($case, $selectedOrganisation, $validateFull)
        );
    }

    private function isValidForCaseWithSelectedOrganisationUncached(EloquentCase $case, EloquentOrganisation $selectedOrganisation, bool $validateFull = true): bool
    {
        if ($case->isWaitingForApproval()) {
            return $this->user->hasPermission(Permission::caseApprove()->value);
        }

        if (!$this->user->hasPermission(self::getRequiredPermissionForStatus($case->getRawOriginal('bco_status')))) {
            return false;
        }

        if (
            $case->getRawOriginal('assigned_organisation_uuid') === null &&
            $case->getRawOriginal('organisation_uuid') === $selectedOrganisation->getRawOriginal('uuid')
        ) {
            // owner organisation can only change user if not assigned to a different organisation
            return !$validateFull || $this->isValidForSelectedOrganisation($selectedOrganisation);
        }

        if ($case->getRawOriginal('assigned_organisation_uuid') === $selectedOrganisation->getRawOriginal('uuid')) {
            // assigned organisation can change user
            return !$validateFull || $this->isValidForSelectedOrganisation($selectedOrganisation);
        }

        return false;
    }

    public function applyToCase(EloquentCase $case): void
    {
        $case->assigned_user_uuid = $this->user->uuid;

        if (isset($case->assignedCaseList) && $case->assignedCaseList->is_queue) {
            $case->assigned_case_list_uuid = null;
        }
    }
}
