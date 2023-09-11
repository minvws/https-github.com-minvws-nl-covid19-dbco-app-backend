<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Admin\CreatePolicyVersionDto;
use App\Dto\Admin\UpdatePolicyVersionDto;
use App\Exceptions\Policy\PolicyVersionStatusTransitionNotValidException;
use App\Exceptions\Policy\PolicyVersionUpdateNotAllowedException;
use App\Models\Eloquent\EloquentCase;
use App\Models\Policy\PolicyVersion;
use App\Repositories\CaseStatusHistoryRepository;
use App\Repositories\Policy\PolicyVersionPopulator;
use App\Repositories\Policy\PolicyVersionRepository;
use App\Services\Policy\PolicyVersion\PolicyVersionStatusTransitionValidator;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PhpOption\None;
use PhpOption\Some;
use Webmozart\Assert\Assert;

use function in_array;
use function is_null;

class PolicyVersionService
{
    public function __construct(
        private readonly PolicyVersionRepository $policyVersionRepository,
        private readonly PolicyVersionStatusTransitionValidator $policyVersionStatusTransitionValidator,
        private readonly PolicyVersionPopulator $policyVersionPopulator,
        private readonly CaseStatusHistoryRepository $caseStatusHistoryRepository,
    )
    {
    }

    /**
     * @return Collection<PolicyVersion>
     */
    public function getPolicyVersions(): Collection
    {
        /** @var Collection<PolicyVersion> */
        return $this->onNoResultPopulate(fn () => $this->policyVersionRepository->getPolicyVersions());
    }

    public function getLatestPolicyVersion(): PolicyVersion
    {
        /** @var PolicyVersion */
        return $this->onNoResultPopulate(fn () => $this->policyVersionRepository->getLatestPolicyVersion());
    }

    public function getActivePolicyVersion(): PolicyVersion
    {
        /** @var PolicyVersion */
        return $this->onNoResultPopulate(fn() => $this->policyVersionRepository->getActivePolicyVersion());
    }

    public function deletePolicyVersion(PolicyVersion $policyVersion): bool
    {
        return $this->policyVersionRepository->deletePolicyVersion($policyVersion);
    }

    public function createPolicyVersion(CreatePolicyVersionDto $dto): PolicyVersion
    {
        return $this->policyVersionRepository->createPolicyVersion($dto);
    }

    /**
     * @throws PolicyVersionUpdateNotAllowedException
     * @throws PolicyVersionStatusTransitionNotValidException
     */
    public function updatePolicyVersion(PolicyVersion $policyVersion, UpdatePolicyVersionDto $dto): PolicyVersion
    {
        if (!$this->isUpdateAllowed($policyVersion, $dto)) {
            throw PolicyVersionUpdateNotAllowedException::create();
        }

        if (!$this->isStatusTransitionValid($policyVersion, $dto)) {
            throw PolicyVersionStatusTransitionNotValidException::create();
        }

        return DB::transaction(
            function () use ($policyVersion, $dto): PolicyVersion {
                if ($dto->status->isDefined()) {
                    $startDate = $dto->startDate->isEmpty() ? $policyVersion->start_date : $dto->startDate->get();
                    $this->checkForCurrentDayActivation($dto, $startDate);
                }
                return $this->policyVersionRepository->updatePolicyVersion($policyVersion, $dto);
            },
        );
    }

    private function checkForCurrentDayActivation(UpdatePolicyVersionDto $dto, CarbonInterface $startDate): void
    {
        Assert::isInstanceOf($startDate, CarbonInterface::class);

        if (!$startDate->isToday() || $dto->status->get() !== PolicyVersionStatus::active()) {
            return;
        }

        // Move current active PolicyVersion to old
        $activePolicyVersion = $this->getActivePolicyVersion();

        $activePolicyVersionDto = new UpdatePolicyVersionDto(
            name: None::create(),
            status: Some::create(PolicyVersionStatus::old()),
            startDate: Some::create($activePolicyVersion->start_date),
        );

        $this->policyVersionRepository->updatePolicyVersion($activePolicyVersion, $activePolicyVersionDto);
    }

    public function getPolicyVersionReadyForActivation(): ?PolicyVersion
    {
        return $this->policyVersionRepository->getActivatablePolicyVersionForCurrentDate();
    }

    public function getPolicyVersionByDate(CarbonInterface $date): PolicyVersion
    {
        $policyVersion = $this->policyVersionRepository->getPolicyVersionByDate($date);
        return $policyVersion ?? $this->getActivePolicyVersion();
    }

    private function isUpdateAllowed(PolicyVersion $policyVersion, UpdatePolicyVersionDto $dto): bool
    {
        $clonedPolicyVersion = clone $policyVersion;
        $clonedPolicyVersion->fill($dto->toArray());

        $hasUpdates = $clonedPolicyVersion->isDirty(['name', 'start_date']);
        $isDraft = $clonedPolicyVersion->status === PolicyVersionStatus::draft();

        return ($hasUpdates && $isDraft) || !$hasUpdates;
    }

    private function isStatusTransitionValid(PolicyVersion $policyVersion, UpdatePolicyVersionDto $dto): bool
    {
        if ($dto->status->isEmpty()) {
            return true;
        }

        return $this->policyVersionStatusTransitionValidator
            ->isValid(
                $policyVersion->status,
                $dto->status->get(),
                $dto->startDate->getOrElse($policyVersion->start_date),
            );
    }

    /**
     * @throws PolicyVersionUpdateNotAllowedException
     */
    public function allowsMutations(PolicyVersion $policyVersion): void
    {
        if ($policyVersion->status !== PolicyVersionStatus::draft()) {
            throw PolicyVersionUpdateNotAllowedException::create();
        }
    }

    public function getPolicyVersionForCase(EloquentCase $eloquentCase): ?PolicyVersion
    {
        if (!in_array($eloquentCase->bco_status, [BCOStatus::completed(), BCOStatus::archived()], true)) {
            return $this->getActivePolicyVersion();
        }

        if (!is_null($eloquentCase->completed_at) && $eloquentCase->completed_at->addWeeks(8)->lessThan(CarbonImmutable::now())) {
            return null;
        }

        $history = $this->caseStatusHistoryRepository->getByStatus($eloquentCase, BCOStatus::archived());
        if ($history && CarbonImmutable::parse($history->changed_at)->addWeeks(8)->lessThan(CarbonImmutable::now())) {
            return null;
        }

        if (!is_null($eloquentCase->policyVersion)) {
            return $eloquentCase->policyVersion;
        }

        if (!is_null($eloquentCase->completed_at)) {
            return $this->getPolicyVersionByDate($eloquentCase->completed_at);
        }

        return $this->getActivePolicyVersion();
    }

    /**
     * @param Closure():(Collection<PolicyVersion>|PolicyVersion|null) $fetch
     *
     * @return Collection<PolicyVersion>|PolicyVersion
     */
    private function onNoResultPopulate(Closure $fetch): Collection|PolicyVersion
    {
        $result = $fetch();

        if (($result instanceof Collection && $result->isEmpty()) || is_null($result)) {
            $this->policyVersionPopulator->populate();

            $result = $fetch();
        }

        if (($result instanceof Collection && $result->isEmpty()) || is_null($result)) {
            throw ValidationException::withMessages(['policy_version' => 'No policy version found.'])->status(Response::HTTP_NOT_FOUND);
        }

        return $result;
    }
}
