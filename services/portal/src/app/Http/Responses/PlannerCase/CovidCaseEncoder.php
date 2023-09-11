<?php

declare(strict_types=1);

namespace App\Http\Responses\PlannerCase;

use App\Helpers\CaseHelper;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\TestResult;
use App\Services\AuthenticationService;
use Illuminate\Auth\AuthenticationException;
use MinVWS\Codable\DateTimeFormatException;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function auth;
use function is_null;

class CovidCaseEncoder implements EncodableDecorator
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @throws AuthenticationException
     * @throws DateTimeFormatException
     */
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof EloquentCase) {
            return;
        }

        /** @var EloquentUser $authenticatedUser */
        $authenticatedUser = auth()->user();

        $container->uuid = $value->uuid;
        $container->caseId = $value->caseId;
        $container->hpzoneNumber = $value->hpzoneNumber;
        $container->testMonsterNumber = $value->testMonsterNumber;
        $container->name = $value->name;
        $container->contactsCount = $value->contactsCount ?? null;
        $container->dateOfBirth->encodeDateTime($value->index->dateOfBirth, 'Y-m-d');
        $container->dateOfTest->encodeDateTime($value->dateOfTest ?? $value->indexSubmittedDateOfTest, 'Y-m-d');
        $container->dateOfSymptomOnset->encodeDateTime($value->date_of_symptom_onset, 'Y-m-d');
        $container->statusIndexContactTracing = $value->status_index_contact_tracing;
        $container->statusExplanation = $value->statusExplanation;
        $container->createdAt = $value->createdAt;
        $container->updatedAt = $value->updatedAt;
        $container->osirisNumber = $value->osiris_number;
        $this->encodeOrganisation($value->organisation, $container->nestedContainer('organisation'));
        $this->encodeOrganisation($value->assignedOrganisation, $container->nestedContainer('assignedOrganisation'));
        $this->encodeCaseList($value->assignedCaseList, $container->nestedContainer('assignedCaseList'));
        $this->encodeUser($value->assignedUser, $container->nestedContainer('assignedUser'));

        $container->isEditable = $authenticatedUser->can('basicEdit', $value);
        $container->isDeletable = $authenticatedUser->can('softDelete', $value);
        $container->isClosable = $value->isClosable();
        $container->isReopenable = $value->isReopenable();
        $container->isAssignable = $this->encodeIsAssignable($authenticatedUser, $value);

        $container->canChangeOrganisation = $value->canChangeOrganisation();

        $container->isApproved = is_null($value->is_approved) ? null : (bool) $value->is_approved;

        if ($this->authService->getRequiredSelectedOrganisation()->uuid === $value->organisation_uuid) {
            $container->label = $value->organisation_label;
        } elseif ($this->authService->getRequiredSelectedOrganisation()->uuid === $value->assigned_organisation_uuid) {
            $container->label = $value->assigned_organisation_label;
        }
        $container->plannerView = CaseHelper::getPlannerView($value, $this->authService->getRequiredSelectedOrganisation()->uuid);
        $container->bcoStatus = $value->bcoStatus->value;
        $container->bcoPhase = $value->bco_phase->value;

        if (isset($value->wasOutsourcedToOrganisationName)) {
            $container->wasOutsourced = true;
            $container->wasOutsourcedToOrganisation->name = $value->wasOutsourcedToOrganisationName;
        } else {
            $container->wasOutsourced = false;
            $container->wasOutsourcedToOrganisation = null;
        }
        $container->lastAssignedUserName = null;
        if (isset($value->last_assigned_user_name)) {
            $container->lastAssignedUserName = $value->last_assigned_user_name;
        } elseif (isset($value->assignedUser)) {
            $container->lastAssignedUserName = $value->assignedUser->name;
        }
        $container->priority = $value->priority;
        $container->caseLabels = $value->caseLabels;
        $container->hasNotes = $value->notes_count > 0;

        $container->testResults = $value->testResults->map(static fn (TestResult $testResult) => $testResult->source->value);

        $container->index_age = $value->index_age;

        $this->encodeVaccinations($value, $container);
    }

    private function encodeOrganisation(?EloquentOrganisation $organisation, EncodingContainer $container): void
    {
        if ($organisation === null) {
            $container->encodeNull();
        } else {
            $container->uuid = $organisation->uuid;
            $container->abbreviation = $organisation->abbreviation;
            $container->name = $organisation->name;
            $container->isCurrent = $this->authService->getRequiredSelectedOrganisation()->uuid === $organisation->uuid;
        }
    }

    private function encodeCaseList(?CaseList $caseList, EncodingContainer $container): void
    {
        if ($caseList === null) {
            $container->encodeNull();
        } else {
            $container->uuid = $caseList->uuid;
            $container->isQueue = $caseList->is_queue;

            if ($caseList->organisation_uuid === $this->authService->getRequiredSelectedOrganisation()->uuid) {
                $container->name = $caseList->name;
            }
        }
    }

    /**
     * @throws AuthenticationException
     */
    private function encodeUser(?EloquentUser $user, EncodingContainer $container): void
    {
        if ($user === null) {
            $container->encodeNull();
        } else {
            $container->uuid = $user->uuid;
            $container->isCurrent = $this->authService->getAuthenticatedUser()->uuid === $user->uuid;

            if ($user->isInOrganisation($this->authService->getRequiredSelectedOrganisation()->uuid)) {
                $container->name = $user->name;
            }
        }
    }

    private function encodeIsAssignable(EloquentUser $user, EloquentCase $value): bool
    {
        return $value->isAssignable($user)
            || $value->isOutsourceable($user)
            || $value->isListable($user);
    }

    private function encodeVaccinations(EloquentCase $value, EncodingContainer $container): void
    {
        $isVaccinated = $value->vaccination->isVaccinated === YesNoUnknown::yes();
        $vaccinationCount = $isVaccinated ? $value->vaccination->vaccinationCount() : null;
        $mostRecentVaccinationDate = $isVaccinated ? $value->vaccination->getInjectionDate(0)?->format('Y-m-d') : null;

        $container->vaccinationCount = $vaccinationCount;
        $container->mostRecentVaccinationDate = $mostRecentVaccinationDate;
    }
}
