<?php

declare(strict_types=1);

namespace App\Services;

use App\Authorization\RoleNotFoundException;
use App\Repositories\PermissionRepository;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\Permission;

use function collect;

class AuthorizationService
{
    private PermissionRepository $permissionRepository;
    private array $permissionCache = [];

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function hasPermission(array $userRoles, string $permission): bool
    {
        foreach ($userRoles as $userRole) {
            if ($this->hasPermissionForRole($userRole, $permission)) {
                return true;
            }
        }

        return false;
    }

    private function hasPermissionForRole(string $userRole, string $permission): bool
    {
        if (!isset($this->permissionCache[$userRole][$permission])) {
            $this->permissionCache[$userRole][$permission] = $this->hasPermissionForRoleUncached($userRole, $permission);
        }

        return $this->permissionCache[$userRole][$permission];
    }

    private function hasPermissionForRoleUncached(string $userRole, string $permission): bool
    {
        try {
            return $this->permissionRepository->getRole($userRole)->hasPermission($permission);
        } catch (RoleNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return array<Permission>
     */
    public function getPermissionsForRoles(array $userRoles): array
    {
        return collect($userRoles)
            ->map(function (string $roleName): Collection {
                try {
                    $role = $this->permissionRepository->getRole($roleName);
                } catch (RoleNotFoundException $roleNotFoundException) {
                    return collect();
                }

                return $role->getPermissions();
            })
            ->flatten()
            ->unique()
            ->all();
    }
}
