<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Authorization\Role;
use App\Authorization\RoleNotFoundException;
use Illuminate\Contracts\Config\Repository as Config;
use MinVWS\DBCO\Enum\Models\Permission;

use function is_array;
use function key_exists;

class ConfigPermissionRepository implements PermissionRepository
{
    public function __construct(protected Config $config)
    {
    }

    /**
     * @throws RoleNotFoundException
     */
    public function getRole(string $roleName): Role
    {
        $rolesAndPermission = $this->config->get('permissions', []);

        if (!$this->isExistingAndValidRole($roleName, $rolesAndPermission)) {
            throw new RoleNotFoundException();
        }

        /** @var array<int,Permission> $permissions */
        $permissions = $rolesAndPermission[$roleName];

        return Role::create($permissions);
    }

    private function isExistingAndValidRole(string $roleName, array $rolesAndPermission): bool
    {
        return key_exists($roleName, $rolesAndPermission)
            && is_array($rolesAndPermission[$roleName]);
    }
}
