<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Dto\Admin\CreatePolicyVersionDto;
use App\Dto\Admin\UpdatePolicyVersionDto;
use App\Models\Policy\PolicyVersion;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use LogicException;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use RuntimeException;
use Webmozart\Assert\Assert;

use function sprintf;

class PolicyVersionRepository
{
    /**
     * @return Collection<PolicyVersion>
     */
    public function getPolicyVersions(): Collection
    {
        $fieldPlaceholders = Collection::make(PolicyVersionStatus::allValues())
            ->map(static fn (): string => '?')
            ->implode(', ');

        return PolicyVersion::query()
            ->orderByRaw(sprintf('FIELD(status, %s)', $fieldPlaceholders), PolicyVersionStatus::allValues())
            ->orderByDesc('start_date')
            ->get();
    }

    public function getLatestPolicyVersion(): ?PolicyVersion
    {
        return PolicyVersion::query()->latest()->first();
    }

    public function getActivePolicyVersion(): ?PolicyVersion
    {
        return PolicyVersion::query()->where('status', PolicyVersionStatus::active())->first();
    }

    public function deletePolicyVersion(PolicyVersion $policyVersion): bool
    {
        try {
            return $policyVersion->delete() ?? false;
        } catch (LogicException) {
            return false;
        }
    }

    public function createPolicyVersion(CreatePolicyVersionDto $dto): PolicyVersion
    {
        return PolicyVersion::query()->create($dto->toEloquentAttributes());
    }

    public function updatePolicyVersion(PolicyVersion $policyVersion, UpdatePolicyVersionDto $dto): PolicyVersion
    {
        if (!$policyVersion->update($dto->toArray())) {
            throw new RuntimeException(sprintf('Failed to update policy version with UUID: "%s"', $policyVersion->uuid));
        }

        return $policyVersion;
    }

    public function getActivatablePolicyVersionForCurrentDate(): ?PolicyVersion
    {
        // Query for PolicyVersion with status = activeSoon and start_date = current_date filtering out time
        $activatablePolicyVersions = PolicyVersion::query()->where('status', PolicyVersionStatus::activeSoon())
            ->whereDate('start_date', CarbonImmutable::now()->toDateString())
            ->get();

        if ($activatablePolicyVersions->count() > 1) {
            throw new LogicException('Multiple activatable PolicyVersions found for current date!');
        }

        return $activatablePolicyVersions->first();
    }

    public function getPolicyVersionByDate(CarbonInterface $date): ?PolicyVersion
    {
        $policyVersion = PolicyVersion::query()->whereIn('status', [PolicyVersionStatus::active(), PolicyVersionStatus::old()])
            ->whereDate('start_date', '<=', $date->toDateString())
            ->orderByDesc('start_date')
            ->first();

        Assert::nullOrIsInstanceOf($policyVersion, PolicyVersion::class);

        return $policyVersion;
    }
}
