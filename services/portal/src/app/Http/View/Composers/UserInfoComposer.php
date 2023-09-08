<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use App\Http\Responses\Organisation\CurrentOrganisationEncoder;
use App\Models\Eloquent\EloquentOrganisation as Organisation;
use App\Models\Eloquent\EloquentUser;
use App\Services\AuthenticationService;
use App\Services\AuthorizationService;
use Illuminate\View\View;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\ValueTypeMismatchException;

use function auth;
use function collect;

class UserInfoComposer
{
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
    ) {
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function compose(View $view): void
    {
        /** @var EloquentUser|null $user */
        $user = auth()->user();

        $view->with('organisation', $this->getOrganisationData());
        $view->with('permissions', $user ? $this->getUserPermissions($user) : null);
        $view->with('user', $user ? $this->getUserData($user) : null);
    }

    private function getUserData(EloquentUser $user): array
    {
        return [
            'name' => $user->name,
            'roles' => $user->getRolesArray(),
            'uuid' => $user->uuid,
        ];
    }

    private function getOrganisationData(): array
    {
        $organisation = $this->authenticationService->getSelectedOrganisation();

        if ($organisation === null) {
            return [];
        }

        $encoder = new Encoder();
        $encoder->getContext()->registerDecorator(Organisation::class, new CurrentOrganisationEncoder());
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);

        try {
            return $encoder->encode($organisation);
        } catch (ValueTypeMismatchException) {
            return [];
        }
    }

    /**
     * @return array<string>
     */
    private function getUserPermissions(EloquentUser $user): array
    {
        return collect($this->authorizationService->getPermissionsForRoles($user->getRolesArray()))
            ->pluck('value')
            ->all();
    }
}
