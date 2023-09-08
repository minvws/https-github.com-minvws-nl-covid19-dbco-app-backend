<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CaseAssignment\GetOptionsMultiRequest;
use App\Http\Requests\Api\CaseAssignment\UpdateMultiRequest;
use App\Http\Requests\Api\CaseAssignment\UpdateRequest;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\PlannerCase\CovidCaseEncoder;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use App\Services\CaseAssignmentConflictService;
use App\Services\CaseAssignmentService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

use function abort_if;
use function array_filter;
use function array_merge;
use function count;
use function response;

class ApiCaseAssignmentController extends ApiController
{
    public function __construct(
        private readonly CaseAssignmentService $caseAssignmentService,
        private readonly CaseAssignmentConflictService $caseAssignmentConflictService,
    ) {
    }

    /**
     * Get assignment options
     */
    #[SetAuditEventDescription('Case toewijzings opties opgehaald')]
    public function getAssignmentOptions(EloquentCase $case): EncodableResponse
    {
        $options = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);
        return new EncodableResponse($options);
    }

    /**
     * Get assignment options multi
     */
    #[SetAuditEventDescription('Multi case toewijzings opties opgehaald')]
    public function getAssignmentOptionsMulti(GetOptionsMultiRequest $request): EncodableResponse
    {
        $options = $this->caseAssignmentService->getAssignmentOptions($request->getCases());
        return new EncodableResponse($options);
    }

    /**
     * Get user assignment options
     */
    #[SetAuditEventDescription('Gebruiker toewijzings opties opgehaald')]
    public function getUserAssignmentOptions(Request $request): EncodableResponse
    {
        $options = $this->caseAssignmentService->getUserAssignmentOptions();
        return new EncodableResponse($options);
    }

    /**
     * Update assignment
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Case toewijzing bijgewerkt')]
    public function updateAssignment(
        EloquentCase $case,
        UpdateRequest $request,
        AuditEvent $auditEvent,
        CovidCaseEncoder $caseEncoder,
    ): BaseResponse {
        $details = array_merge($request->assignment(), ['bcoStatus' => $case->bcoStatus->value]);
        $auditEvent->objectDetails('case', 'properties', $details);

        $conflictingCaseUuids = $this->caseAssignmentConflictService->findConflictingAssignments(
            [$case->uuid],
            $request->getStringOrNull('staleSince') ?? '',
        );

        if ($conflictingCaseUuids->isNotEmpty()) {
            return $this->caseAssignmentConflictService->createUpdateAssignmentResponse([$case->uuid], $conflictingCaseUuids);
        }

        $assignment = $this->caseAssignmentService->assignmentForArray($request->assignment());
        $this->caseAssignmentService->assignCase($case, $assignment);

        return EncodableResponseBuilder::create($case)
            ->withContext(static function (EncodingContext $context) use ($caseEncoder): void {
                                                $context->registerDecorator(EloquentCase::class, $caseEncoder);
            })
                                            ->build();
    }

    /**
     * Update assignment multi
     */
    #[SetAuditEventDescription('Multi case toewijzing bijgewerkt')]
    public function updateAssignmentMulti(UpdateMultiRequest $request, AuditEvent $auditEvent): JsonResponse
    {
        /** @var Collection $cases */
        $cases = EloquentCase::find($request->cases, ['uuid', 'bco_status']);
        $cases = $cases->keyBy('uuid');

        $conflictingCaseUuids = $this->caseAssignmentConflictService->findConflictingAssignments(
            $request->cases,
            $request->getStringOrNull('staleSince') ?? '',
        );

        if ($conflictingCaseUuids->count() === count($request->cases)) {
            return $this->caseAssignmentConflictService->createUpdateAssignmentResponse($request->cases, $conflictingCaseUuids);
        }

        $assignableCases = array_filter($request->cases, static function ($covidCaseUuid) use ($conflictingCaseUuids) {
            return !$conflictingCaseUuids->contains('covidcase_uuid', '===', $covidCaseUuid);
        }) ?? [];

        $requestedAssignment = $request->assignment();
        $assignment = $this->caseAssignmentService->assignmentForArray($requestedAssignment);
        $this->caseAssignmentService->assignCases($assignableCases, $assignment);

        foreach ($assignableCases as $caseUuid) {
            $details = array_merge(
                $requestedAssignment,
                ['bcoStatus' => $cases->get($caseUuid)->bcoStatus->value ?? 'unknown'],
            );
            $auditEvent->object(AuditObject::create('case', $caseUuid)->detail('properties', $details));
        }

        return $this->caseAssignmentConflictService->createUpdateAssignmentResponse($request->cases, $conflictingCaseUuids);
    }

    /**
     * Assign next case
     *
     * @throws AuthenticationException
     */
    #[SetAuditEventDescription('Volgende case toegewezen')]
    public function nextCase(CaseList $caseList, AuditEvent $auditEvent): JsonResponse
    {
        $auditEvent->getObjects()[0]->identifier($caseList->uuid); // else we will log "default" for the default queue

        $caseUuid = null;

        try {
            $caseUuid = $this->caseAssignmentService->assignNextCase($caseList);
        } catch (UnauthorizedException $e) {
            Log::error($e->getMessage());
        }

        abort_if($caseUuid === null, 404);
        return response()->json(['caseUuid' => $caseUuid], Response::HTTP_OK);
    }
}
