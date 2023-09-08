<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AuditObjectHelper;
use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Requests\Api\ApiRequest;
use App\Models\Eloquent\EloquentCase;
use App\Services\CaseFragmentService;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;

use function is_string;

class ApiCaseValidationStatusController extends ApiController
{
    use ValidatesModels;

    public function __construct(
        private readonly CaseFragmentService $caseFragmentService,
        private readonly ResponseFactory $response,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getCaseValidationStatus(
        ApiRequest $request,
        AuditEvent $auditEvent,
        EloquentCase $case,
    ): JsonResponse {
        $caseAuditObject = AuditObject::create('case', $case->uuid);
        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $case);
        $auditEvent->object($caseAuditObject);

        $requestFilter = $request->getStringOrNull('filter');
        $filter = is_string($requestFilter) ? [$requestFilter] : [];
        $validationResult = $this->caseFragmentService->validateAllFragments($case, $filter);

        return $this->response->json(['validationResult' => $validationResult]);
    }
}
