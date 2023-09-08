<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helpers\SearchableHash;
use App\Jobs\ExportCaseToOsiris;
use App\Jobs\UpdatePlaceCounters;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Services\Chores\ChoreService;
use MinVWS\DBCO\Enum\Models\BCOStatus;

use function array_key_exists;

class EloquentCaseObserver
{
    public function __construct(
        private readonly ChoreService $choreService,
        private readonly SearchableHash $searchableHash,
    ) {
    }

    public function updated(EloquentCase $case): void
    {
        $this->updatePlaceCounters($case);
    }

    public function saving(EloquentCase $case): void
    {
        $this->preStoreContact($case);
    }

    public function deleting(EloquentCase $case): void
    {
        if ($case->isForceDeleting()) {
            $this->choreService->forceDeleteByCaseUuids([$case->uuid]);
        }
    }

    public function deleted(EloquentCase $case): void
    {
        ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::DELETED_STATUS);
    }

    private function updatePlaceCounters(EloquentCase $case): void
    {
        if (
                !$this->attributeExistsAndIsDirty($case, 'date_of_symptom_onset') &&
                !$this->attributeExistsAndIsDirty($case, 'date_of_test') &&
                !$this->attributeExistsAndIsDirty($case, 'created_at') &&
                !$this->attributeHasChangedToOrFromValue($case, 'bco_status', BCOStatus::archived())
        ) {
            return;
        }

        foreach ($case->contexts as $context) {
            $place = $context->place;

            if (!$place) {
                continue;
            }

            UpdatePlaceCounters::dispatch($place->uuid);
        }
    }

    private function preStoreContact(EloquentCase $case): void
    {
        if (!$this->attributeExistsAndIsDirty($case, 'contact')) {
            return;
        }

        $contact = $case->contact;
        $index = $case->index;

        if (empty($index->lastname)) {
            return;
        }

        if (!empty($contact->phone)) {
            $case->search_phone = $this->searchableHash->hashForLastNameAndPhone($index->lastname, $contact->phone);
        }

        if (!empty($contact->email)) {
            $case->search_email = $this->searchableHash->hashForLastNameAndEmail($index->lastname, $contact->email);
        }
    }

    private function attributeExistsAndIsDirty(EloquentCase $case, string $attribute): bool
    {
        return array_key_exists($attribute, $case->getAttributes()) && $case->isDirty($attribute);
    }

    private function attributeHasChangedToOrFromValue(EloquentCase $case, string $attribute, mixed $value): bool
    {
        if (!$this->attributeExistsAndIsDirty($case, $attribute)) {
            return false;
        }

        return $case->getOriginal($attribute) === $value || $case->getAttribute($attribute) === $value;
    }
}
