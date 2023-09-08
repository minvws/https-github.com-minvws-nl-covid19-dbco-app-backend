<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CaseLabel;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\Priority;

class CasePlanningData
{
    public ?string $assignedCaseListUuid;
    public ?string $organisationLabel;
    public ?string $pseudoBsnGuid;
    public Priority $priority;

    /** @var array<CaseLabel> $caseLabels */
    public array $caseLabels;
    public ?string $testMonsterNumber;
    public ?string $caseId = null;
    public ?ContactTracingStatus $statusIndexContactTracing = null;
    public AutomaticAddressVerificationStatus $automaticAddressVerificationStatus;

    /**
     * @param array<CaseLabel> $caseLabels;
     */
    public function __construct(
        ?string $assignedCaseListUuid,
        ?string $organisationLabel,
        ?string $pseudoBsnGuid,
        Priority $priority,
        array $caseLabels,
        ?string $testMonsterNumber,
        ?ContactTracingStatus $statusIndexContactTracing,
        AutomaticAddressVerificationStatus $automaticAddressVerificationStatus,
    ) {
        $this->assignedCaseListUuid = $assignedCaseListUuid;
        $this->organisationLabel = $organisationLabel;
        $this->pseudoBsnGuid = $pseudoBsnGuid;
        $this->priority = $priority;
        $this->caseLabels = $caseLabels;
        $this->testMonsterNumber = $testMonsterNumber;
        $this->statusIndexContactTracing = $statusIndexContactTracing;
        $this->automaticAddressVerificationStatus = $automaticAddressVerificationStatus;
    }
}
