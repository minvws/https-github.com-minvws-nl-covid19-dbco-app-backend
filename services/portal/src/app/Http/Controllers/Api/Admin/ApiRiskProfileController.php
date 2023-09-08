<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Attributes\RequestHasFixedValuesQueryFilter;
use App\Http\Controllers\Api\Admin\Attributes\RequestQueryFilter;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\RiskProfile\UpdateRiskProfileRequest;
use App\Http\Responses\Api\Admin\RiskProfileEncoder as AdminRiskProfileEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Services\RiskProfileService;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

use function array_merge;

class ApiRiskProfileController extends ApiController
{
    #[RequestQueryFilter('person')]
    protected ?PolicyPersonType $policyPersonType = null;

    public function __construct(private RiskProfileService $riskProfileService)
    {
        $this->authorizeResource(RiskProfile::class);
    }

    #[SetAuditEventDescription('Lijst van risico profilen ophalen')]
    #[RequestHasFixedValuesQueryFilter('person', PolicyPersonType::class, required: true)]
    public function index(PolicyVersion $policyVersion): EncodableResponse
    {
        $riskProfiles = $this->riskProfileService->getRiskProfilesByPolicyVersion($policyVersion, $this->policyPersonType);

        return $this->encodeRiskProfileResponse($riskProfiles);
    }

    #[SetAuditEventDescription('Risico profiel ophalen')]
    public function show(PolicyVersion $policyVersion, RiskProfile $riskProfile): EncodableResponse
    {
        return $this->encodeRiskProfileResponse($riskProfile);
    }

    #[SetAuditEventDescription('Risico profiel updaten')]
    public function update(UpdateRiskProfileRequest $riskProfileRequest, PolicyVersion $policyVersion, RiskProfile $riskProfile, AuditEvent $auditEvent): EncodableResponse
    {
        $auditObject = AuditObject::create('risk-profile', $riskProfile->uuid);
        $properties = array_merge(['policyVersionUuid' => $policyVersion->uuid], $riskProfileRequest->toArray());
        $auditObject->detail('properties', $properties);
        $auditEvent->object($auditObject);

        $validatedAttributes = (array) $riskProfileRequest->validated();
        $updatedRiskProfile = $this->riskProfileService->updateRiskProfile($riskProfile, $validatedAttributes);

        return $this->encodeRiskProfileResponse($updatedRiskProfile);
    }

    /**
     * @param RiskProfile|Collection<RiskProfile> $riskProfile
     */
    private function encodeRiskProfileResponse(RiskProfile|Collection $riskProfile): EncodableResponse
    {
        return EncodableResponseBuilder::create($riskProfile)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(RiskProfile::class, new AdminRiskProfileEncoder());
            })
            ->build();
    }
}
