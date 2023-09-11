<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helpers\SearchableHash;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task\Inform;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\InformStatus;
use MinVWS\DBCO\Enum\Models\Relationship;
use RuntimeException;
use SodiumException;
use Throwable;

use function is_null;

class EloquentTaskObserver
{
    public function __construct(
        private readonly EncryptionHelper $encryptionHelper,
        private readonly SearchableHash $searchableHash,
    ) {
    }

    private function hasStoreKeyForTask(EloquentTask $task): bool
    {
        return $this->encryptionHelper->hasStoreKey(StorageTerm::short(), $task->created_at);
    }

    /**
     * Handle the task "retrieved" event.
     *
     * @throws SodiumException
     */
    public function retrieved(EloquentTask $task): void
    {
        if (!$this->hasStoreKeyForTask($task)) {
            return;
        }

        $this->postLoadGeneral($task);
        $this->postLoadInform($task);
    }

    /**
     * @throws SodiumException
     */
    private function postLoadGeneral(EloquentTask $task): void
    {
        $this->postLoadGeneralFromQuestionnaire($task);
    }

    /**
     * @throws SodiumException
     */
    private function postLoadGeneralFromQuestionnaire(EloquentTask $task): void
    {
        if (empty($task->general->relationship)) {
            $relationship = $task->relationshipAnswer();
            if ($relationship !== null) {
                $unsealedRelationship = $this->encryptionHelper->unsealOptionalStoreValue($relationship->spv_value);
                $task->general->relationship = Relationship::tryFromOptional($unsealedRelationship);
            }
        }

        // we don't have a field that is 1:1 comparable to the remarks field in the questionnaire, we use the
        // nature field if it isn't filled already because it comes pretty close
        if (empty($task->general->nature)) {
            $remarks = $task->remarksAnswer();
            if ($remarks !== null) {
                $unsealedRemarks = $this->encryptionHelper->unsealOptionalStoreValue($remarks->spv_value);
                $task->general->nature = $unsealedRemarks;
            }
        }

        $contactDetails = $task->contactDetailsAnswer();
        if ($contactDetails === null) {
            return;
        }

        $task->general->firstname = !empty($task->general->firstname)
            ? $task->general->firstname
            :
            $this->encryptionHelper->unsealOptionalStoreValue($contactDetails->ctd_firstname);
        $task->general->lastname = !empty($task->general->lastname)
            ? $task->general->lastname
            :
            $this->encryptionHelper->unsealOptionalStoreValue($contactDetails->ctd_lastname);
        $task->general->email = !empty($task->general->email)
            ? $task->general->email
            :
            $this->encryptionHelper->unsealOptionalStoreValue($contactDetails->ctd_email);
        $task->general->phone = !empty($task->general->phone)
            ? $task->general->phone
            :
            $this->encryptionHelper->unsealOptionalStoreValue($contactDetails->ctd_phonenumber);
    }

    public function saving(EloquentTask $task): void
    {
        if (!$this->hasStoreKeyForTask($task)) {
            return;
        }

        $this->preStoreGeneral($task);
        $this->preStoreInform($task);
    }

    private function preStoreGeneral(EloquentTask $task): void
    {
        if (!isset($task->general)) {
            return;
        }

        if ($task->isDirty('label') && empty($task->general->firstname)) {
            $task->general->firstname = $task->label;
        } elseif (!$task->isDirty('label')) {
            if (!empty($task->general->firstname) && !empty($task->general->lastname)) {
                $task->label = $task->general->firstname . ' ' . $task->general->lastname;
            } elseif (!empty($task->general->firstname)) {
                $task->label = $task->general->firstname;
            }
        }

        if ($task->general->lastname && $task->general->email && !empty($task->general->lastname) && !empty($task->general->email)) {
            $task->search_email = $this->searchableHash->hashForLastNameAndEmail($task->general->lastname, $task->general->email);
        }

        if (!$task->general->lastname || !$task->general->phone || empty($task->general->lastname) || empty($task->general->phone)) {
            return;
        }

        $task->search_phone = $this->searchableHash->hashForLastNameAndPhone($task->general->lastname, $task->general->phone);
    }

    private function preStoreInform(EloquentTask $task): void
    {
        // We set the informedByStaffAt manually based on the inform status changes.
        // This date is important for the Metrics functionality to determine if the
        // 'informed' event should be registered.
        //
        // Note: this is temporary until the Portal supports setting the date from the UI.
        $task->informed_by_staff_at = $this->determineInformedByStaffAt($task);
    }

    private function determineInformedByStaffAt(EloquentTask $task): ?DateTimeInterface
    {
        if (
            empty($task->informed_by_staff_at)
            && (
                $task->inform_status === InformStatus::informed()
                || $task->inform_status === InformStatus::emailSent()
            )
        ) {
            return CarbonImmutable::now();
        }

        if (
            !empty($task->informed_by_staff_at)
            && (
                $task->inform_status === InformStatus::uninformed()
                || $task->inform_status === InformStatus::unreachable()
            )
        ) {
            return null;
        }

        return $task->informed_by_staff_at;
    }

    /**
     * When the inform fragment property shareIndexNameWithContact is empty, we try to fill it with
     * the submitted value by the app.
     */
    private function postLoadInform(EloquentTask $task): void
    {
        if (!is_null($task->inform->shareIndexNameWithContact)) {
            return;
        }

        $mention = $task->mentionAnswer();
        if ($mention === null) {
            return;
        }

        try {
            if ($mention->spv_value !== null) {
                $unsealedMention = $this->encryptionHelper->unsealOptionalStoreValue($mention->spv_value);
                $task->inform->shareIndexNameWithContact = $unsealedMention === Inform::SHARE_INDEX_NAME_WITH_CONTACT_TRUE;
            }
        } catch (Throwable) {
            throw new RuntimeException('Could not decrypt mention answer.');
        }
    }
}
