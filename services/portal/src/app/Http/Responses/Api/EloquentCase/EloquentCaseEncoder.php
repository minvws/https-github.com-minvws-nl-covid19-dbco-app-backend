<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\EloquentCase;

use App\Helpers\CaseHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Policies\EloquentCasePolicy;
use App\Services\AuthenticationService;
use App\Services\CaseLockService;
use App\Services\ContextService;
use App\Services\PolicyVersionService;
use App\Services\TaskService;
use Illuminate\Auth\AuthenticationException;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;
use function is_null;

class EloquentCaseEncoder implements EncodableDecorator
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
        private readonly EloquentCasePolicy $casePolicy,
        private readonly CaseLockService $caseLockService,
        private readonly ContextService $contextService,
        private readonly TaskService $taskService,
        private readonly PolicyVersionService $policyVersionService,
    ) {
    }

    /**
     * @throws AuthenticationException
     */
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof EloquentCase);

        // Make a nested container as this is needed for the front-end
        $container = $container->nestedContainer('case');

        $container->uuid = $value->uuid;
        $container->owner = $value->owner;
        $container->organisationUuid = $value->organisation_uuid ?? null;
        $container->assignedUserUuid = $value->assigned_user_uuid ?? null;
        $container->assignedCaseListUuid = $value->assigned_case_list_uuid ?? null;
        $container->assignedName = $value->assignedName ?? null;
        $container->assignedOrganisationUuid = $value->assigned_organisation_uuid ?? null;
        $container->assignedOrganisationLabel = $value->assigned_organisation_label ?? null;
        $container->organisation = $this->encodeOrganisation($value->organisation);

        $container->bcoStatus = $value->bcoStatus;
        $container->bcoPhase = $value->bco_phase;
        $container->indexStatus = $value->indexStatus;

        $container->statusIndexContactTracing = $value->status_index_contact_tracing ?? null;
        $container->statusExplanation = $value->statusExplanation;

        $container->name = $value->name;
        $container->source = $value->source ?? null;
        $container->caseId = $value->caseId;
        $container->testMonsterNumber = $value->testMonsterNumber ?? null;
        $container->symptomatic = $value->symptomatic ?? null;
        $container->indexSubmittedAt = $value->indexSubmittedAt ?? null;
        $container->windowExpiresAt = $value->windowExpiresAt ?? null;
        $container->pairingExpiresAt = $value->pairingExpiresAt ?? null;

        $container->exportId = $value->exportId ?? null;
        $container->exportedAt = $value->exportedAt ?? null;
        $container->osirisNumber = $value->osiris_number;

        $container->completedAt = $value->completedAt ?? null;
        $container->pseudoBsnGuid = $value->pseudoBsnGuid ?? null;
        $container->schemaVersion = $value->getSchemaVersion()->getVersion();
        $container->priority = $value->priority;
        $container->caseLabels = $value->caseLabels ?? [];
        $container->organisationLabel = $value->organisation_label ?? null;

        $container->searchDateOfBirth = $value->searchDateOfBirth ?? null;
        $container->searchEmail = $value->searchEmail ?? null;
        $container->searchPhone = $value->searchPhone ?? null;

        $container->expiresAt = $value->expiresAt ?? null;
        $container->copiedAt = $value->copiedAt ?? null;
        $container->dateOfSymptomOnset = $value->date_of_symptom_onset?->format('Y-m-d');
        $container->dateOfTest = $value->date_of_test?->format('Y-m-d');
        $container->episodeStartDate = $value->episode_start_date->format('Y-m-d');

        $container->createdAt = $value->createdAt;
        $container->updatedAt = $value->updatedAt;
        $container->deletedAt = $value->deletedAt;

        $container->isApproved = $value->isApproved ?? null;
        $container->automaticAddressVerificationStatus = $value->automatic_address_verification_status;

        $hasLock = $this->caseLockService->hasCaseLock($value, $this->authenticationService->getAuthenticatedUser());

        $container->userCanEdit = $this->encodeUserCanEdit($value, $hasLock);
        $container->isLocked = $hasLock;

        $container->contextContagiousCount = $this->contextService->countContextsByCaseAndGroup($value, 'contagious');

        $container->taskCount = $this->taskService->countTaskGroupsForCase($value);

        $container->plannerView = CaseHelper::getPlannerView($value, $this->authenticationService->getRequiredSelectedOrganisation()->uuid);

        $container->policyVersion = $this->encodePolicyVersion($value);
    }

    /**
     * @throws AuthenticationException
     */
    protected function encodeUserCanEdit(EloquentCase $eloquentCase, bool $hasLock): bool
    {
        return $this->casePolicy->edit($this->authenticationService->getAuthenticatedUser(), $eloquentCase) && !$hasLock;
    }

    protected function encodeOrganisation(EloquentOrganisation $organisation): array
    {
        return [
            'uuid' => $organisation->uuid,
            'abbreviation' => $organisation->abbreviation ?? null,
            'externalId' => $organisation->external_id,
            'hpZoneCode' => $organisation->hpZoneCode,
            'name' => $organisation->name,
            'phoneNumber' => $organisation->phone_number,
            'bcoPhase' => $organisation->bco_phase,
            'bcoStatus' => $organisation->bco_status,
            'isCurrent' => $this->authenticationService->getRequiredSelectedOrganisation()->uuid === $organisation->uuid,
        ];
    }

    private function encodePolicyVersion(EloquentCase $eloquentCase): ?array
    {
        $policyVersion = $this->policyVersionService->getPolicyVersionForCase($eloquentCase);

        if (is_null($policyVersion)) {
            return null;
        }

        return [
            'uuid' => $policyVersion->uuid,
            'name' => $policyVersion->name,
            'startDate' => $policyVersion->start_date->format('Y-m-d'),
        ];
    }
}
