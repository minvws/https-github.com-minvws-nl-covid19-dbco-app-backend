<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\Chore\Resource;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Services\AuthenticationService;
use App\Services\Chores\ChoreService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\DBCO\Enum\Models\ResourcePermission;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function assert;
use function response;

class ApiAssignmentController extends ApiController
{
    public function __construct(
        private readonly ChoreService $choreService,
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    #[SetAuditEventDescription('Dossier toegewezen')]
    public function assignSingleCase(EloquentCase $case): JsonResponse
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        assert($user instanceof EloquentUser);

        if (!$user->allowedCasesByToken([$case->uuid])) {
            throw new HttpException(403, 'No access to the cases');
        }

        $expiresAt = CarbonImmutable::now()->addDay();

        $choreUuid = $this->choreService->createChore(
            organisationUuid: $this->authenticationService->getRequiredSelectedOrganisation()->uuid,
            resource: new Resource($case->getVersionedResourceType(), $case->uuid),
            owner: new Resource('bco-user', $user->uuid),
            requiredPermission: ResourcePermission::edit(),
            expiresAt: $expiresAt,
        );
        $this->choreService->assignChore(choreId: $choreUuid, userUuid: $user->uuid, expiresAt: $expiresAt);

        return response()->json(['message' => 'Successfully assigned case.']);
    }
}
