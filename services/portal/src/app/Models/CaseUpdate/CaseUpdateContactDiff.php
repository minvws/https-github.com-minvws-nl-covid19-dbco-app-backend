<?php

declare(strict_types=1);

namespace App\Models\CaseUpdate;

use App\Models\Eloquent\CaseUpdateContact;

class CaseUpdateContactDiff
{
    private CaseUpdateContact $caseUpdateContact;

    /** @var array<CaseUpdateFragmentDiff> */
    private array $fragmentDiffs = [];

    public function __construct(CaseUpdateContact $caseUpdateContact)
    {
        $this->caseUpdateContact = $caseUpdateContact;
    }

    public function getCaseUpdateContact(): CaseUpdateContact
    {
        return $this->caseUpdateContact;
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
}
