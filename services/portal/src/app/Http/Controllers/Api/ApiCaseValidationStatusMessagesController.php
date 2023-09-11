<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AuditObjectHelper;
use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Requests\Api\ApiRequest;
use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Eloquent\EloquentCase;
use App\Services\CaseFragmentService;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Http\JsonResponse;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;

use function array_merge;
use function array_values;
use function is_string;

class ApiCaseValidationStatusMessagesController extends ApiController
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
    public function getCaseValidationStatusMessages(
        ApiRequest $request,
        AuditEvent $auditEvent,
        EloquentCase $case,
    ): JsonResponse {
        $caseAuditObject = AuditObject::create('case', $case->uuid);
        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $case);
        $auditEvent->object($caseAuditObject);

        $requestFilter = $request->getStringOrNull('filter');
        $filter = is_string($requestFilter) ? [$requestFilter] : [];
        $validationResult = $this->caseFragmentService->validateAllFragments($case, $filter, false);

        $response = $this->buildResponseFromValidationResult($validationResult);

        return $this->response->json($response);
    }

    private function buildResponseFromValidationResult(array $validationResult): array
    {
        $response = [
            Validatable::SEVERITY_LEVEL_FATAL => [],
            Validatable::SEVERITY_LEVEL_WARNING => [],
            Validatable::SEVERITY_LEVEL_NOTICE => [],
        ];

        foreach ($validationResult as $fragment) {
            foreach ($fragment as $severityLevel => $fragmentValidationResult) {
                /** @var MessageBag $messageBag */
                $messageBag = $fragmentValidationResult['errors'];

                $response[$severityLevel] = array_merge(...array_values($messageBag->getMessages()));
            }
        }

        return $response;
    }
}
