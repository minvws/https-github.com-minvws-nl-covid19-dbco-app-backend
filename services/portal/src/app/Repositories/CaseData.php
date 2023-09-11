<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CaseLabel;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\Priority;

class CaseData
{
    public ?string $owner;
    public BCOPhase $bcoPhase;
    public ?string $organisationUuid;
    public ?string $caseId;
    public ?string $testMonsterNumber;
    public ?string $assignedUserUuid = null;
    public ?string $assignedCaseListUuid;
    public ?string $organisationLabel;
    public ?string $pseudoBsnGuid;
    public Priority $priority;

     /** @var array<CaseLabel> $caseLabels */
    public array $caseLabels;
    public ?ContactTracingStatus $statusIndexContactTracing = null;
    public AutomaticAddressVerificationStatus $automaticAddressVerificationStatus;

    /**
     * @param array<CaseLabel> $caseLabels
     */
    public function __construct(
        ?string $owner,
        BCOPhase $bcoPhase,
        ?string $organisationUuid,
        ?string $assignedCaseListUuid,
        ?string $organisationLabel,
        ?string $pseudoBsnGuid,
        Priority $priority,
        array $caseLabels,
        ?string $testMonsterNumber,
        ?string $caseId,
        ?ContactTracingStatus $statusIndexContactTracing,
        AutomaticAddressVerificationStatus $automaticAddressVerificationStatus,
    ) {
        $this->owner = $owner;
        $this->bcoPhase = $bcoPhase;
        $this->organisationUuid = $organisationUuid;
        $this->assignedCaseListUuid = $assignedCaseListUuid;
        $this->organisationLabel = $organisationLabel;
        $this->pseudoBsnGuid = $pseudoBsnGuid;
        $this->priority = $priority;
        $this->caseLabels = $caseLabels;
        $this->testMonsterNumber = $testMonsterNumber;
        $this->caseId = $caseId;
        $this->statusIndexContactTracing = $statusIndexContactTracing;
        $this->automaticAddressVerificationStatus = $automaticAddressVerificationStatus;
    }
}
