<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Eloquent\CaseLabel;
use Carbon\CarbonInterface;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use MinVWS\DBCO\Enum\Models\Priority;

/**
 * @deprecated use \App\Models\Eloquent\EloquentCase, see DBCO-3004
 */
class CovidCase
{
    public string $uuid;

    public ?string $owner = null;

    public ?string $organisationUuid = null;

    public ?string $assignedUserUuid = null;
    public ?string $assignedOrganisationUuid = null;
    public ?string $assignedCaseListUuid = null;

    public ?string $assignedName = null;

    public ?bool $isApproved;
    public BCOStatus $bcoStatus;
    public IndexStatus $indexStatus;
    public BCOPhase $bcoPhase;
    public ?ContactTracingStatus $statusIndexContactTracing;
    public string $statusExplanation = '';

    public ?string $name = null;

    public ?string $source = null;
    public ?string $caseId = null;
    public ?string $searchDateOfBirth = null;
    public ?string $searchEmail = null;
    public ?string $searchPhone = null;

    public ?CarbonInterface $dateOfSymptomOnset = null;
    public ?CarbonInterface $dateOfTest = null;
    public ?string $testMonsterNumber = null;
    public ?bool $symptomatic = null;

    public ?CarbonInterface $indexSubmittedAt = null;
    public ?CarbonInterface $createdAt = null;
    public ?CarbonInterface $updatedAt = null;
    public ?CarbonInterface $deletedAt = null;
    public ?CarbonInterface $windowExpiresAt = null;
    public ?CarbonInterface $pairingExpiresAt = null;
    public ?CarbonInterface $expiresAt = null;

    public ?string $exportId = null;
    public ?CarbonInterface $exportedAt = null;
    public ?CarbonInterface $copiedAt = null;

    public ?CarbonInterface $completedAt = null;

    public ?string $pseudoBsnGuid = null;
    public ?int $schemaVersion = null;
    public Priority $priority;

    /** @var array<CaseLabel> $caseLabels */
    public array $caseLabels = [];

    public ?string $organisationLabel = null;
    public ?string $assignedOrganisationLabel = null;

    public ?Organisation $organisation = null;

    public AutomaticAddressVerificationStatus $automaticAddressVerificationStatus;

    public function __construct()
    {
        $this->statusIndexContactTracing = ContactTracingStatus::new();
        $this->bcoStatus = BCOStatus::draft();
        $this->indexStatus = IndexStatus::initial();
        $this->priority = Priority::none();
        $this->automaticAddressVerificationStatus = AutomaticAddressVerificationStatus::unchecked();
    }

    public function calculateSourcePeriodStart(): ?CarbonInterface
    {
        $date = null;

        if ($this->dateOfSymptomOnset) {
            $date = $this->dateOfSymptomOnset->clone()->subDays(14);
        } elseif ($this->dateOfTest) {
            $date = $this->dateOfTest->clone()->subDays(14);
        }

        return $date;
    }

    public function calculateSourcePeriodEnd(): ?CarbonInterface
    {
        $date = $this->calculateContagiousPeriodStart();

        // Protection against edge cases
        if ($date !== null) {
            $date = $date->subDay();
        }

        return $date;
    }

    public function calculateContagiousPeriodStart(): ?CarbonInterface
    {
        $date = null;
        if ($this->symptomatic) {
            if ($this->dateOfSymptomOnset) {
                $date = $this->dateOfSymptomOnset->clone();
                $date->addDays(-2);
            }
        } else {
            if ($this->dateOfTest) {
                $date = $this->dateOfTest->clone();
            }
        }
        return $date;
    }
}
