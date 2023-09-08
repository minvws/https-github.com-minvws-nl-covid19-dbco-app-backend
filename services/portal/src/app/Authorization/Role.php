<?php

declare(strict_types=1);

namespace App\Authorization;

use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\Permission;

class Role
{
    /**
     * @param array<int,Permission> $permissions
     */
    public function __construct(private array $permissions)
    {
    }

    /**
     * @param array<int,Permission> $permissions
     */
    public static function create(array $permissions): Role
    {
        return new Role(permissions: $permissions);
    }

    /**
     * Return a collection of permissions.
     *
     * @return Collection<int,Permission>
     */
    public function getPermissions(): Collection
    {
        return Collection::make($this->permissions);
    }

    /**
     * Validate if the Role has the given permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->getPermissions()
            ->contains(static fn (Permission $p): bool => $p->value === $permission);
    }
}
