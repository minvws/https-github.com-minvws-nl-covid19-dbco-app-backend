<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Authorization\Role;
use App\Authorization\RoleNotFoundException;

interface PermissionRepository
{
    /**
     * @throws RoleNotFoundException
     */
    public function getRole(string $roleName): Role;
}
