<?php

declare(strict_types=1);

namespace App\Providers\Auth;

use Illuminate\Support\Collection;
use Laravel\Socialite\Two\User;

use function collect;

class IdentityHubUser extends User
{
    public array $departments = [];
    public array $organisations = [];
    public array $roles = [];

    public function getDepartments(): Collection
    {
        return collect($this->departments);
    }

    public function getOrganisations(): Collection
    {
        return collect($this->organisations);
    }

    public function getRoles(): Collection
    {
        return collect($this->roles);
    }
}
