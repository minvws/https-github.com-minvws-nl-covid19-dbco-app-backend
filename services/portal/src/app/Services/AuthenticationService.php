<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RequiredOrganisationNotFoundException;
use App\Models\Eloquent\BelongsToOrganisation;
use App\Models\Eloquent\EloquentOrganisation as Organisation;
use App\Models\Eloquent\EloquentUser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\App;

use function auth;

class AuthenticationService
{
    private ?Organisation $selectedOrganisation = null;

    /**
     * Is user logged in?
     */
    public function isLoggedIn(): bool
    {
        return auth()->user() instanceof EloquentUser;
    }

    /**
     * @throws AuthenticationException
     */
    public function getAuthenticatedUser(): EloquentUser
    {
        $user = auth()->user();

        if (!$user instanceof EloquentUser) {
            throw new AuthenticationException();
        }

        return $user;
    }

    public function logout(): void
    {
        $this->selectedOrganisation = null;
        auth()->logout();
    }

    public function getSelectedOrganisation(): ?Organisation
    {
        if (isset($this->selectedOrganisation)) {
            return $this->selectedOrganisation;
        }

        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // TODO: this should be based on a selected organisation that is stored in the session
        $authenticatable = auth()->user();
        if (!$authenticatable instanceof BelongsToOrganisation) {
            return null;
        }

        $selectedOrganisation = $authenticatable->getOrganisation();
        if (!App::runningUnitTests()) {
            $this->selectedOrganisation = $selectedOrganisation;
        }

        return $selectedOrganisation;
    }

    public function getRequiredSelectedOrganisation(): Organisation
    {
        $organisation = $this->getSelectedOrganisation();
        if ($organisation === null) {
            throw new RequiredOrganisationNotFoundException('Selected organisation required, but no organisation selected!');
        }

        return $organisation;
    }

    /**
     * @throws AuthenticationException
     */
    public function hasRole(string $role): bool
    {
        return $this->getAuthenticatedUser()->hasRole($role);
    }
}
