<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\PolicyGuideline\UpdatePolicyGuidelineRequest;
use App\Http\Responses\Api\Admin\PolicyGuidelineEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Services\PolicyGuidelineService;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;

use function array_merge;

class ApiPolicyGuidelineController extends ApiController
{
    public function __construct(private PolicyGuidelineService $policyGuidelineService)
    {
        $this->authorizeResource(PolicyGuideline::class);
    }

    #[SetAuditEventDescription('Lijst van beleidsrichtlijnen ophalen')]
    public function index(PolicyVersion $policyVersion): EncodableResponse
    {
        $policyGuidelines = $this->policyGuidelineService->getPolicyGuidelinesByPolicyVersion($policyVersion);

        return $this->encodePolicyGuidelineResponse($policyGuidelines);
    }

    #[SetAuditEventDescription('Beleidsrichtlijn ophalen')]
    public function show(PolicyVersion $policyVersion, PolicyGuideline $policyGuideline): EncodableResponse
    {
        return $this->encodePolicyGuidelineResponse($policyGuideline);
    }

    #[SetAuditEventDescription('Update beleidsrichtlijn')]
    public function update(
        UpdatePolicyGuidelineRequest $policyGuidelineRequest,
        PolicyVersion $policyVersion,
        PolicyGuideline $policyGuideline,
        AuditEvent $auditEvent,
    ): EncodableResponse
    {
        $auditObject = AuditObject::create('policy-guideline', $policyGuideline->uuid);
        $properties = array_merge(['policyVersionUuid' => $policyVersion->uuid], $policyGuidelineRequest->toArray());
        $auditObject->detail('properties', $properties);
        $auditEvent->object($auditObject);

        $validatedAttributes = (array) $policyGuidelineRequest->validated();
        $updatedPolicyGuideline = $this->policyGuidelineService->updatePolicyGuideline($policyGuideline, $validatedAttributes);

        return $this->encodePolicyGuidelineResponse($updatedPolicyGuideline);
    }

    /**
     * @param PolicyGuideline|Collection<PolicyGuideline> $policyGuideline
     */
    private function encodePolicyGuidelineResponse(PolicyGuideline|Collection $policyGuideline): EncodableResponse
    {
        return EncodableResponseBuilder::create($policyGuideline)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(PolicyGuideline::class, new PolicyGuidelineEncoder());
            })
            ->build();
    }
}
