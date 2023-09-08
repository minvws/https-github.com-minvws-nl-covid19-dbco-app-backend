<?php

declare(strict_types=1);

namespace App\Services\CaseUpdate;

use App\Models\CaseUpdate\CaseUpdateDiff;
use App\Models\Eloquent\CaseUpdate;
use App\Models\Eloquent\CaseUpdateContact;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\Intake;
use App\Models\Eloquent\IntakeContact;
use App\Repositories\CaseRepository;
use App\Repositories\CaseUpdateRepository;
use App\Repositories\Intake\IntakeRepository;
use App\Repositories\TaskRepository;
use App\Schema\Update\UpdateException;
use App\Schema\Update\UpdateValidationException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\TaskGroup;

use function count;

class CaseUpdateService
{
    private CaseUpdateRepository $caseUpdateRepository;
    private CaseRepository $caseRepository;
    private TaskRepository $taskRepository;
    private IntakeRepository $intakeRepository;

    public function __construct(
        CaseUpdateRepository $caseUpdateRepository,
        CaseRepository $caseRepository,
        TaskRepository $taskRepository,
        IntakeRepository $intakeRepository,
    ) {
        $this->caseUpdateRepository = $caseUpdateRepository;
        $this->caseRepository = $caseRepository;
        $this->taskRepository = $taskRepository;
        $this->intakeRepository = $intakeRepository;
    }

    public function getCaseUpdateByUuid(string $uuid): ?CaseUpdate
    {
        return $this->caseUpdateRepository->getCaseUpdateByUuid($uuid);
    }

    public function getCaseUpdatesForCase(EloquentCase $case): array
    {
        return $this->caseUpdateRepository->getCaseUpdatesForCase($case);
    }

    /**
     * @throws CaseUpdateValidationException
     * @throws CaseUpdateException
     */
    public function getCaseUpdateDiff(EloquentCase $case, CaseUpdate $caseUpdate): CaseUpdateDiff
    {
        return $this->caseUpdateRepository->getCaseUpdateDiffForCase($caseUpdate, $case);
    }

    /**
     * @throws UpdateException
     */
    private function applyCaseUpdateFragmentToCase(EloquentCase $case, CaseUpdate $caseUpdate, string $fragmentName, array $fields, array &$validationResults): void
    {
        $caseUpdateFragment = $this->caseUpdateRepository->getCaseUpdateFragment($caseUpdate, $fragmentName);
        if ($caseUpdateFragment === null) {
            return;
        }

        try {
            $this->caseUpdateRepository->applyCaseUpdateFragmentToCase($caseUpdateFragment, $case, $fields);
        } catch (UpdateValidationException $e) {
            $key = $fragmentName;
            $validationResults[$key] = $e->getValidationResult();
        }
    }

    /**
     * @throws UpdateValidationException
     * @throws UpdateException
     */
    private function applyCaseUpdateToCase(EloquentCase $case, CaseUpdate $caseUpdate, ApplyCaseUpdateOptions $options, array &$validationResults): void
    {
        if (!$options->hasCaseFragments()) {
            return;
        }

        foreach ($options->getCaseFragmentNames() as $fragmentName) {
            $fields = $options->getCaseFragmentFields($fragmentName);
            $this->applyCaseUpdateFragmentToCase($case, $caseUpdate, $fragmentName, $fields, $validationResults);
        }

        if (count($validationResults) > 0) {
            return;
        }

        $this->caseRepository->save($case);
    }

    /**
     * @throws UpdateException
     */
    private function applyCaseUpdateContactFragmentToContact(EloquentTask $contact, CaseUpdateContact $caseUpdateContact, string $fragmentName, array $fields, array &$validationResults): void
    {
        $caseUpdateContactFragment = $this->caseUpdateRepository->getCaseUpdateContactFragment($caseUpdateContact, $fragmentName);
        if ($caseUpdateContactFragment === null) {
            return;
        }

        try {
            $this->caseUpdateRepository->applyCaseUpdateContactFragmentToContact($caseUpdateContactFragment, $contact, $fields);
        } catch (UpdateValidationException $e) {
            $key = "contacts.{$caseUpdateContactFragment->caseUpdateContact->uuid}.{$fragmentName}";
            $validationResults[$key] = $e->getValidationResult();
        }
    }

    /**
     * @throws UpdateValidationException
     * @throws UpdateException
     */
    private function applyCaseUpdateContactToCase(EloquentCase $case, CaseUpdate $caseUpdate, string $uuid, ApplyCaseUpdateOptions $options, array &$validationResults): void
    {
        $caseUpdateContact = $this->caseUpdateRepository->getCaseUpdateContact($caseUpdate, $uuid);
        if ($caseUpdateContact === null) {
            return;
        }

        $contact = $caseUpdateContact->contact;
        if ($contact === null) {
            $contact = $this->taskRepository->createTaskForCase($case);
            $contact->label = $caseUpdateContact->label;
            $contact->taskGroup = $caseUpdateContact->contactGroup;
            $contact->source = $caseUpdate->source;
        }

        foreach ($options->getContactFragmentNames($uuid) as $fragmentName) {
            $fields = $options->getContactFragmentFields($uuid, $fragmentName);
            $this->applyCaseUpdateContactFragmentToContact($contact, $caseUpdateContact, $fragmentName, $fields, $validationResults);
        }

        if (count($validationResults) > 0) {
            return;
        }

        $this->taskRepository->save($contact);
    }

    /**
     * @throws UpdateValidationException
     * @throws UpdateException
     */
    private function applyCaseUpdateToContacts(EloquentCase $case, CaseUpdate $caseUpdate, ApplyCaseUpdateOptions $options, array &$validationResults): void
    {
        if (!$options->hasContacts()) {
            return;
        }

        foreach ($options->getContactUuids() as $uuid) {
            $this->applyCaseUpdateContactToCase($case, $caseUpdate, $uuid, $options, $validationResults);
        }
    }

    /**
     * @throws UpdateException
     * @throws CaseUpdateValidationException
     *
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function applyCaseUpdate(EloquentCase $case, CaseUpdate $caseUpdate, ApplyCaseUpdateOptions $options): void
    {
        DB::transaction(function () use ($case, $caseUpdate, $options): void {
            $validationResults = [];

            $this->applyCaseUpdateToCase($case, $caseUpdate, $options, $validationResults);
            $this->applyCaseUpdateToContacts($case, $caseUpdate, $options, $validationResults);

            if (count($validationResults) > 0) {
                throw new CaseUpdateValidationException('Case update contains invalid data!', $validationResults);
            }

            $this->caseUpdateRepository->deleteCaseUpdate($caseUpdate);
        });
    }

    public function convertIntakeToCaseUpdate(Intake $intake, EloquentCase $case): CaseUpdate
    {
        return DB::transaction(function () use ($intake, $case) {
            $caseUpdate = $this->caseUpdateRepository->makeCaseUpdateForCase($case);
            $caseUpdate->uuid = $intake->uuid;
            $caseUpdate->source = $intake->source;
            $caseUpdate->received_at = $intake->received_at;
            $caseUpdate->createdAt = CarbonImmutable::now();
            $this->caseUpdateRepository->saveCaseUpdateForCase($caseUpdate, $case);
            $this->convertIntakeFragmentsToCaseUpdateFragments($intake, $caseUpdate);
            $this->convertIntakeContactsToCaseUpdateContacts($intake, $caseUpdate);
            $this->caseRepository->addCaseLabels($case, $intake->caseLabels);
            $this->intakeRepository->deleteIntake($intake);
            return $caseUpdate;
        });
    }

    private function convertIntakeFragmentsToCaseUpdateFragments(Intake $intake, CaseUpdate $caseUpdate): void
    {
        foreach ($intake->fragments as $intakeFragment) {
            $caseUpdateFragment = $this->caseUpdateRepository->makeCaseUpdateFragmentForCaseUpdate($caseUpdate);
            $caseUpdateFragment->name = $intakeFragment->name;
            $caseUpdateFragment->data = $intakeFragment->data;
            $caseUpdateFragment->version = $intakeFragment->version;
            $this->caseUpdateRepository->saveCaseUpdateFragmentForCaseUpdate($caseUpdateFragment, $caseUpdate);
        }
    }

    private function convertIntakeContactsToCaseUpdateContacts(Intake $intake, CaseUpdate $caseUpdate): void
    {
        foreach ($intake->contacts as $intakeContact) {
            $caseUpdateContact = $this->caseUpdateRepository->makeCaseUpdateContactForCaseUpdate($caseUpdate);
            $caseUpdateContact->contactGroup = TaskGroup::positiveSource();
            $caseUpdateContact->label = 'Bronpersoon intakevragenlijst';
            $this->caseUpdateRepository->saveCaseUpdateContactForCaseUpdate($caseUpdateContact, $caseUpdate);
            $this->convertIntakeContactFragmentsToCaseUpdateContactFragments($intakeContact, $caseUpdateContact);
        }
    }

    private function convertIntakeContactFragmentsToCaseUpdateContactFragments(IntakeContact $intakeContact, CaseUpdateContact $caseUpdateContact): void
    {
        foreach ($intakeContact->fragments as $intakeContactFragment) {
            $caseUpdateContactFragment = $this->caseUpdateRepository->makeCaseUpdateContactFragmentForCaseUpdateContact($caseUpdateContact);
            $caseUpdateContactFragment->name = $intakeContactFragment->name;
            $caseUpdateContactFragment->data = $intakeContactFragment->data;
            $caseUpdateContactFragment->version = $intakeContactFragment->version;
            $this->caseUpdateRepository->saveCaseUpdateContactFragmentForCaseUpdateContact($caseUpdateContactFragment, $caseUpdateContact);
        }
    }
}
