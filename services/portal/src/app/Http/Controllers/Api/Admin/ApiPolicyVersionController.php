<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\PolicyVersion\CreatePolicyVersionRequest;
use App\Http\Requests\Api\Admin\PolicyVersion\UpdatePolicyVersionRequest;
use App\Http\Responses\Api\Admin\PolicyVersionEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Policy\PolicyVersion;
use App\Services\PolicyVersionService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;

class ApiPolicyVersionController extends ApiController
{
    public function __construct(private readonly PolicyVersionService $policyVersionService)
    {
        $this->authorizeResource(PolicyVersion::class);
    }

    #[SetAuditEventDescription('Lijst van beleidsversies ophalen')]
    public function index(): EncodableResponse
    {
        $policyVersions = $this->policyVersionService->getPolicyVersions();

        return $this->encodePolicyVersionResponse($policyVersions);
    }

    #[SetAuditEventDescription('Beleidsversie ophalen')]
    public function show(PolicyVersion $policyVersion): EncodableResponse
    {
        return $this->encodePolicyVersionResponse($policyVersion);
    }

    #[SetAuditEventDescription('Beleidsversie verwijderen')]
    public function destroy(PolicyVersion $policyVersion, ResponseFactory $response, AuditEvent $auditEvent): Response|JsonResponse
    {
        $auditObject = AuditObject::create('policy-version', $policyVersion->uuid);
        $auditEvent->object($auditObject);

        return $this->policyVersionService->deletePolicyVersion($policyVersion)
            ? $response->noContent()
            : $response->json(status: 404);
    }

    #[SetAuditEventDescription('Beleidsversie aanmaken')]
    public function store(CreatePolicyVersionRequest $request, AuditEvent $auditEvent): EncodableResponse
    {
        $policyVersion = $this->policyVersionService->createPolicyVersion($request->getDto());

        $auditObject = AuditObject::create('policy-version', $policyVersion->uuid);
        $auditObject->detail('properties', $request->toArray());
        $auditEvent->object($auditObject);

        return $this->encodePolicyVersionResponse($policyVersion, status: Response::HTTP_CREATED);
    }

    #[SetAuditEventDescription('Beleidsversie updaten')]
    public function update(PolicyVersion $policyVersion, UpdatePolicyVersionRequest $request, AuditEvent $auditEvent): EncodableResponse
    {
        $auditObject = AuditObject::create('policy-version', $policyVersion->uuid);
        $auditObject->detail('properties', $request->getDto()->toArray());
        $auditEvent->object($auditObject);

        $policyVersion = $this->policyVersionService->updatePolicyVersion($policyVersion, $request->getDto());

        return $this->encodePolicyVersionResponse($policyVersion);
    }

    /**
     * @param PolicyVersion|Collection<PolicyVersion> $policyVersion
     */
    private function encodePolicyVersionResponse(PolicyVersion|Collection $policyVersion, int $status = 200): EncodableResponse
    {
        return EncodableResponseBuilder::create($policyVersion, $status)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(PolicyVersion::class, new PolicyVersionEncoder());
            })
            ->build();
    }
}
