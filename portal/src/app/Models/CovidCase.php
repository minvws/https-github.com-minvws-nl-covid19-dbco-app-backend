<?php

namespace App\Models;

use Jenssegers\Date\Date;

class CovidCase
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_PAIRED = 'paired';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_PENDING_EXPORT = 'pending_export';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_TIMEOUT = 'timeout';
    public const STATUS_EXPIRED = 'expired';

    public string $uuid;

    public string $owner;
    public ?string $assignedUuid;
    public string $organisationUuid;

    public string $status;

    public ?string $name;

    public ?string $caseId;

    public ?Date $dateOfSymptomOnset;

    public bool $hasExportables;

    public ?Date $indexSubmittedAt;
    public ?Date $updatedAt;
    public ?Date $windowExpiresAt;
    public ?Date $pairingExpiresAt;

    public array $tasks = array();

    public function caseStatus()
    {
        if ($this->status == self::STATUS_ARCHIVED) {
            return self::STATUS_ARCHIVED;
        } else if ($this->status == self::STATUS_DRAFT) {
            return self::STATUS_DRAFT;
        } else if ($this->status == self::STATUS_OPEN && $this->pairingExpiresAt !== null && $this->pairingExpiresAt->isFuture()) {
            return self::STATUS_OPEN;
        } else if ($this->status == self::STATUS_OPEN && $this->pairingExpiresAt !== null && $this->pairingExpiresAt->isPast()) {
            return self::STATUS_TIMEOUT;
        } else if ($this->status == self::STATUS_PAIRED && $this->windowExpiresAt->isPast() && $this->indexSubmittedAt == null) {
            return self::STATUS_EXPIRED;
        } else if ($this->status == self::STATUS_PAIRED && $this->windowExpiresAt->isFuture() && $this->indexSubmittedAt == null) {
            return self::STATUS_PAIRED;
        } else if ($this->status == self::STATUS_PAIRED && $this->windowExpiresAt->isFuture() && $this->hasExportables) {
            return self::STATUS_DELIVERED;
        } else if ($this->status == self::STATUS_PAIRED && $this->windowExpiresAt->isPast() && $this->hasExportables) {
            return self::STATUS_PENDING_EXPORT;
        } else if ($this->status == self::STATUS_PAIRED && $this->windowExpiresAt->isFuture() && $this->indexSubmittedAt != null && !$this->hasExportables) {
            return self::STATUS_PROCESSED;
        } else if ($this->status == self::STATUS_PAIRED && $this->windowExpiresAt->isPast() && $this->indexSubmittedAt != null && !$this->hasExportables) {
            return self::STATUS_COMPLETED;
        }
    }

    public static function statusLabel($status)
    {
        $labels = [
            self::STATUS_DRAFT => 'Concept',
            self::STATUS_OPEN => 'Koppelcode gedeeld',
            self::STATUS_PAIRED => 'Nog niets ontvangen',
            self::STATUS_DELIVERED => 'Gegevens aangeleverd',
            self::STATUS_PENDING_EXPORT => 'Nog niet verwerkt',
            self::STATUS_PROCESSED => 'Verwerkt',
            self::STATUS_COMPLETED => 'Afgerond',
            self::STATUS_ARCHIVED => 'Gearchiveerd',
            self::STATUS_TIMEOUT => 'Koppelcode verlopen',
            self::STATUS_EXPIRED => 'Verlopen',
        ];
        return $labels[$status];
    }

    public function isEditable()
    {
        $status = $this->caseStatus();
        if ($status == self::STATUS_COMPLETED) return false;
        if ($status == self::STATUS_ARCHIVED) return false;
        if ($status == self::STATUS_EXPIRED) return false;
        return true;
    }

}
