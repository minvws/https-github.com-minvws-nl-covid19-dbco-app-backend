<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CaseUpdate\CaseUpdateContactDiff;
use App\Models\CaseUpdate\CaseUpdateDiff;
use App\Models\CaseUpdate\CaseUpdateFragmentDiff;
use App\Models\Eloquent\CaseUpdate;
use App\Models\Eloquent\CaseUpdateContact;
use App\Models\Eloquent\CaseUpdateContactFragment;
use App\Models\Eloquent\CaseUpdateFragment;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Schema\Update\UpdateException;
use App\Schema\Update\UpdateValidationException;
use App\Services\CaseUpdate\CaseUpdateException;
use App\Services\CaseUpdate\CaseUpdateValidationException;
use Throwable;

use function count;

class CaseUpdateRepository
{
    public function getCaseUpdatesForCase(EloquentCase $case): array
    {
        return $case->caseUpdates->all();
    }

    public function getCaseUpdateByUuid(string $uuid): ?CaseUpdate
    {
        $update = CaseUpdate::query()->find($uuid);
        return $update instanceof CaseUpdate ? $update : null;
    }

    public function getCaseUpdateFragment(CaseUpdate $caseUpdate, string $fragmentName): ?CaseUpdateFragment
    {
        $fragment = $caseUpdate->fragments()->where('name', '=', $fragmentName)->first();
        return $fragment instanceof CaseUpdateFragment ? $fragment : null;
    }

    public function getCaseUpdateContact(CaseUpdate $caseUpdate, string $caseUpdateContactUuid): ?CaseUpdateContact
    {
        $contact = $caseUpdate->contacts()->find($caseUpdateContactUuid);
        return $contact instanceof CaseUpdateContact ? $contact : null;
    }

    public function getCaseUpdateContactFragment(CaseUpdateContact $caseUpdateContact, string $fragmentName): ?CaseUpdateContactFragment
    {
        $fragment = $caseUpdateContact->fragments()->where('name', '=', $fragmentName)->first();
        return $fragment instanceof CaseUpdateContactFragment ? $fragment : null;
    }

    /**
     * @throws UpdateException
     */
    private function addCaseUpdateFragmentDiffs(CaseUpdateDiff $caseUpdateDiff, array &$validationResults): void
    {
        foreach ($caseUpdateDiff->getCaseUpdate()->fragments as $fragment) {
            $key = $fragment->name;

            try {
                $diff = $fragment->toUpdateDiff();
                if (!$diff->isEmpty()) {
                    $caseUpdateDiff->addFragmentDiff(new CaseUpdateFragmentDiff($key, $fragment->name, $diff));
                }
            } catch (UpdateValidationException $e) {
                $validationResults[$key] = $e->getValidationResult();
            }
        }
    }

    /**
     * @throws UpdateException
     */
    private function addCaseUpdateContactDiffs(CaseUpdateDiff $caseUpdateDiff, array &$validationResults): void
    {
        foreach ($caseUpdateDiff->getCaseUpdate()->contacts as $contact) {
            $caseUpdateContactDiff = new CaseUpdateContactDiff($contact);
            foreach ($contact->fragments ?? [] as $fragment) {
                $key = "contacts.{$fragment->caseUpdateContact->uuid}.{$fragment->name}";

                try {
                    $diff = $fragment->toUpdateDiff();
                    if (!$diff->isEmpty()) {
                        $caseUpdateContactDiff->addFragmentDiff(new CaseUpdateFragmentDiff($key, $fragment->name, $diff));
                    }
                } catch (UpdateValidationException $e) {
                    $validationResults[$key] = $e->getValidationResult();
                }
            }

            $caseUpdateDiff->addContactDiff($caseUpdateContactDiff);
        }
    }

    /**
     * @throws CaseUpdateException|CaseUpdateValidationException
     */
    public function getCaseUpdateDiffForCase(CaseUpdate $caseUpdate, EloquentCase $case): CaseUpdateDiff
    {
        $validationResults = [];

        $caseUpdateDiff = new CaseUpdateDiff($caseUpdate);

        try {
            $this->addCaseUpdateFragmentDiffs($caseUpdateDiff, $validationResults);
            $this->addCaseUpdateContactDiffs($caseUpdateDiff, $validationResults);
        } catch (Throwable $e) {
            throw new CaseUpdateException('Case update is invalid: ' . $e->getMessage(), 0, $e->getPrevious());
        }

        if (count($validationResults) > 0) {
            throw new CaseUpdateValidationException('Case update contains invalid data!', $validationResults);
        }

        return $caseUpdateDiff;
    }

    public function makeCaseUpdateForCase(EloquentCase $case): CaseUpdate
    {
        /** @var CaseUpdate $caseUpdate */
        $caseUpdate = $case->caseUpdates()->make();
        $caseUpdate->case()->associate($case);
        return $caseUpdate;
    }

    public function saveCaseUpdateForCase(CaseUpdate $caseUpdate, EloquentCase $case): void
    {
        $case->caseUpdates()->save($caseUpdate);
    }

    public function makeCaseUpdateFragmentForCaseUpdate(CaseUpdate $caseUpdate): CaseUpdateFragment
    {
        /** @var CaseUpdateFragment $caseUpdateFragment */
        $caseUpdateFragment = $caseUpdate->fragments()->make();
        $caseUpdateFragment->caseUpdate()->associate($caseUpdate);
        $caseUpdateFragment->receivedAt = $caseUpdate->received_at;
        return $caseUpdateFragment;
    }

    public function saveCaseUpdateFragmentForCaseUpdate(CaseUpdateFragment $caseUpdateFragment, CaseUpdate $caseUpdate): void
    {
        $caseUpdate->fragments()->save($caseUpdateFragment);
    }

    public function makeCaseUpdateContactForCaseUpdate(CaseUpdate $caseUpdate): CaseUpdateContact
    {
        /** @var CaseUpdateContact $caseUpdateContact */
        $caseUpdateContact = $caseUpdate->contacts()->make();
        $caseUpdateContact->caseUpdate()->associate($caseUpdate);
        $caseUpdateContact->received_at = $caseUpdate->received_at;
        return $caseUpdateContact;
    }

    public function saveCaseUpdateContactForCaseUpdate(CaseUpdateContact $caseUpdateContact, CaseUpdate $caseUpdate): void
    {
        $caseUpdate->contacts()->save($caseUpdateContact);
    }

    public function makeCaseUpdateContactFragmentForCaseUpdateContact(CaseUpdateContact $caseUpdateContact): CaseUpdateContactFragment
    {
        /** @var CaseUpdateContactFragment $caseUpdateContactFragment */
        $caseUpdateContactFragment = $caseUpdateContact->fragments()->make();
        $caseUpdateContactFragment->caseUpdateContact()->associate($caseUpdateContact);
        $caseUpdateContactFragment->received_at = $caseUpdateContact->received_at;
        return $caseUpdateContactFragment;
    }

    public function saveCaseUpdateContactFragmentForCaseUpdateContact(CaseUpdateContactFragment $caseUpdateContactFragment, CaseUpdateContact $caseUpdateContact): void
    {
        $caseUpdateContact->fragments()->save($caseUpdateContactFragment);
    }

    /**
     * @throws UpdateValidationException|UpdateException
     */
    public function applyCaseUpdateFragmentToCase(CaseUpdateFragment $fragment, EloquentCase $case, array $fields): void
    {
        $fragment->applyToCase($case, $fields);
    }

    /**
     * @throws UpdateValidationException|UpdateException
     */
    public function applyCaseUpdateContactFragmentToContact(CaseUpdateContactFragment $fragment, EloquentTask $contact, array $fields): void
    {
        $fragment->applyToContact($contact, $fields);
    }

    public function deleteCaseUpdate(CaseUpdate $caseUpdate): void
    {
        $caseUpdate->delete();
    }
}
