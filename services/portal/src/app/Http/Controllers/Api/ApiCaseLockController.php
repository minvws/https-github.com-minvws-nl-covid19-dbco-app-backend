<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Responses\CaseLock\CaseLockEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentCaseLock;
use App\Services\AuthenticationService;
use App\Services\CaseLockService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MinVWS\Codable\EncodingContext;

use function response;

class ApiCaseLockController extends ApiController
{
    public function __construct(
        private CaseLockService $caseLockService,
        private AuthenticationService $authenticationService,
        private CaseLockEncoder $caseLockEncoder,
    ) {
    }

    public function hasCaseLock(EloquentCase $case): EncodableResponse|JsonResponse
    {
        $caseLock = $this->caseLockService->getCaseLock($case);

        if ($caseLock === null) {
            return response()->json([], 204);
        }

        return $this->encodeResponse($caseLock);
    }

    /**
     * @throws AuthenticationException
     */
    public function refreshCaseLock(EloquentCase $case): EncodableResponse
    {
        $caseLock = $this->caseLockService->refreshCaseLock($case, $this->authenticationService->getAuthenticatedUser());

        return $this->encodeResponse($caseLock);
    }

    /**
     * @throws AuthenticationException
     */
    public function removeCaseLock(EloquentCase $case): JsonResponse
    {
        $this->caseLockService->removeCaseLock($case, $this->authenticationService->getAuthenticatedUser());

        return response()->json('OK');
    }

    protected function encodeResponse(EloquentCaseLock $caseLock): EncodableResponse
    {
        return EncodableResponseBuilder::create($caseLock)
            ->withContext(function (EncodingContext $context): void {
                $context->registerDecorator(EloquentCaseLock::class, $this->caseLockEncoder);
            })->build();
    }
}
