<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\PolicyGuidelineRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

use function is_null;

class PolicyGuidelineService
{
    public function __construct(private readonly PolicyGuidelineRepository $policyGuidelineRepository)
    {
    }

    /**
     * @return Collection<PolicyGuideline>
     */
    public function getPolicyGuidelinesByPolicyVersion(PolicyVersion $policyVersion): Collection
    {
        return $this->policyGuidelineRepository->getPolicyGuidelinesByPolicyVersion($policyVersion);
    }

    public function getPolicyGuidelineByIdentifierAndPolicyVersion(string $identifier, PolicyVersion $policyVersion): PolicyGuideline
    {
        $policyGuideline = $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion($identifier, $policyVersion);

        if (is_null($policyGuideline)) {
            throw ValidationException::withMessages(['policy_guideline' => 'No policy guideline found.'])->status(Response::HTTP_NOT_FOUND);
        }

        return $policyGuideline;
    }

    public function updatePolicyGuideline(PolicyGuideline $policyGuideline, array $attributes): PolicyGuideline
    {
        return $this->policyGuidelineRepository->updatePolicyGuideline($policyGuideline, $attributes);
    }
}
