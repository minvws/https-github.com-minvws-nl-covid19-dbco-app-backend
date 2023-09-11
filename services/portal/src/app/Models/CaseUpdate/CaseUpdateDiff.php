<?php

declare(strict_types=1);

namespace App\Models\CaseUpdate;

use App\Models\Eloquent\CaseUpdate;

class CaseUpdateDiff
{
    private CaseUpdate $caseUpdate;

    /** @var array<CaseUpdateFragmentDiff> */
    private array $fragmentDiffs = [];

    /** @var array<CaseUpdateContactDiff> */
    private array $contactDiffs = [];

    public function __construct(CaseUpdate $caseUpdate)
    {
        $this->caseUpdate = $caseUpdate;
    }

    public function getCaseUpdate(): CaseUpdate
    {
        return $this->caseUpdate;
    }

    /**
     * @return array<CaseUpdateFragmentDiff>
     */
    public function getFragmentDiffs(): array
    {
        return $this->fragmentDiffs;
    }

    public function addFragmentDiff(CaseUpdateFragmentDiff $fragmentDiff): void
    {
        $this->fragmentDiffs[] = $fragmentDiff;
    }

    /**
     * @return array<CaseUpdateContactDiff>
     */
    public function getContactDiffs(): array
    {
        return $this->contactDiffs;
    }

    public function addContactDiff(CaseUpdateContactDiff $contactDiff): void
    {
        $this->contactDiffs[] = $contactDiff;
    }
}
