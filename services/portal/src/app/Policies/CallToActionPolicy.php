<?php

declare(strict_types=1);

namespace App\Policies;

use App\Exceptions\CallToActionNotFoundHttpException;
use App\Exceptions\CallToActionUnavailableException;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Policies\Traits\AccessibleByCase;
use Illuminate\Http\Request;
use MinVWS\DBCO\Enum\Models\ChoreResourceType;
use MinVWS\DBCO\Enum\Models\Permission;
use Webmozart\Assert\Assert;

use function assert;

class CallToActionPolicy
{
    use AccessibleByCase;

    public function __construct(private readonly Request $request)
    {
    }

    public function get(EloquentUser $eloquentUser, CallToAction $callToAction): bool
    {
        return $eloquentUser->can(Permission::callToAction()->value) && $this->isCallToActionAccessibleByUser($eloquentUser, $callToAction);
    }

    public function create(EloquentUser $eloquentUser): bool
    {
        if ($this->isCovidCaseCallToActionRequest()) {
            $case = EloquentCase::find($this->request->get('resource_uuid'));
            if ($case === null) {
                return false;
            }

            assert($case instanceof EloquentCase);
            if ($this->canEditCase($eloquentUser, $case)) {
                return $eloquentUser->can(Permission::caseCreateCallToAction()->value);
            }

            if ($eloquentUser->hasToken()) {
                return $eloquentUser->allowedCaseCallToActionsByToken([$this->request->get('resource_uuid')]);
            }
        }

        return false;
    }

    private function isCovidCaseCallToActionRequest(): bool
    {
        return $this->request->has('resource_uuid')
            && $this->request->has('resource_type')
            && $this->request->get('resource_type') === ChoreResourceType::covidCase()->value;
    }

    /**
     * @throws CallToActionNotFoundHttpException
     * @throws CallToActionUnavailableException
     */
    private function isCallToActionAccessibleByUser(EloquentUser $eloquentUser, CallToAction $callToAction): bool
    {
        $chore = $callToAction->chore()->getResults();
        Assert::notNull($chore);

        // If Call to Action is already picked up by another user
        if ($chore->hasAssignment() && $chore->assignment->user->uuid !== $eloquentUser->uuid) {
            throw new CallToActionUnavailableException();
        }

        return true;
    }
}
