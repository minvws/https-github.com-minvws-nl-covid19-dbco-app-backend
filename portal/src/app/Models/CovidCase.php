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
    public const STATUS_UNKNOWN = 'unknown';

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

    /**
     * @var Task[]
     */
    public array $tasks = array();

    public function caseStatus(): string
    {
        switch ($this->status) {
            case self::STATUS_ARCHIVED:
                return self::STATUS_ARCHIVED;

            case self::STATUS_DRAFT:
                return self::STATUS_DRAFT;

            case self::STATUS_OPEN:
                if ($this->pairingExpiresAt !== null) {
                    if ($this->pairingExpiresAt->isFuture()) {
                        return self::STATUS_OPEN;
                    } else {
                        return self::STATUS_TIMEOUT;
                    }
                }
                break;

            case self::STATUS_PAIRED:
                if ($this->windowExpiresAt !== null) {
                    if ($this->indexSubmittedAt === null && $this->windowExpiresAt->isFuture()) {
                        return self::STATUS_PAIRED;
                    } elseif ($this->indexSubmittedAt === null && $this->windowExpiresAt->isPast()) {
                        return self::STATUS_EXPIRED;
                    } elseif ($this->hasExportables && $this->windowExpiresAt->isFuture()) {
                        return self::STATUS_DELIVERED;
                    } elseif ($this->hasExportables && $this->windowExpiresAt->isPast()) {
                        return self::STATUS_PENDING_EXPORT;
                    } elseif ($this->windowExpiresAt->isFuture() && $this->indexSubmittedAt !== null && !$this->hasExportables) {
                        return self::STATUS_PROCESSED;
                    } elseif ($this->windowExpiresAt->isPast() && $this->indexSubmittedAt !== null && !$this->hasExportables) {
                        return self::STATUS_COMPLETED;
                    }
                }
                break;
        }

        return self::STATUS_UNKNOWN;
    }

    public static function statusLabel($status): string
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
            self::STATUS_UNKNOWN => 'Onbekend'
        ];
        return $labels[$status] ?? $labels[self::STATUS_UNKNOWN];
    }

    public function isEditable()
    {
        $status = $this->caseStatus();
        if ($status == self::STATUS_COMPLETED) return false;
        if ($status == self::STATUS_ARCHIVED) return false;
        if ($status == self::STATUS_EXPIRED) return false;
        return true;
    }

    public function calculateContagiousPeriodStart(): Date
    {
        $date = $this->dateOfSymptomOnset->clone();
        $date->addDays(-2);
        return $date;
    }

}
