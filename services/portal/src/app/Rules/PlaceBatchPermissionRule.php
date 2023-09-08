<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\Place;
use App\Services\AuthenticationService;
use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;

use function app;
use function count;
use function is_array;

class PlaceBatchPermissionRule implements Rule
{
    private EloquentOrganisation $organisation;
    private string $policyAction;
    private EloquentUser $user;

    public function __construct(string $policyAction, EloquentUser $user)
    {
        $authenticationService = app(AuthenticationService::class);
        $this->organisation = $authenticationService->getRequiredSelectedOrganisation();
        $this->policyAction = $policyAction;
        $this->user = $user;
    }

    /**
     * @inheritdoc
     *
     * @param array $value Contains an array with place uuid's
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('$value is not an array!');
        }

        $verifiedUuidsCount = Place::query()
            ->whereIn('uuid', $value)
            ->where('organisation_uuid', '=', $this->organisation->uuid)
            ->count();

        return $verifiedUuidsCount === count($value) && $this->user->can($this->policyAction, Place::class);
    }

    public function message(): string
    {
        return 'no permission for supplied places';
    }
}
