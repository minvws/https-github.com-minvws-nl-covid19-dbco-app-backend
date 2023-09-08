<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class CaseLabelRepository
{
    public const CASE_LABEL_CODE_NOT_IDENTIFIED = 'not_identified';
    public const CASE_LABEL_REPEAT_RESULT = 'repeat_result';
    public const CASE_LABEL_HEALTHCARE = 'healthcare';
    public const CASE_LABEL_SOCIETAL_INSTITUTION = 'social_institution';
    public const CASE_LABEL_SCHOOL = 'school';

    /**
     * @return Collection<CaseLabel>
     */
    public function getLabelsByCode(Collection $codes): Collection
    {
        return CaseLabel::whereIn('code', $codes)->get();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getLabelByCode(string $code): CaseLabel
    {
        return CaseLabel::where('code', $code)->firstOrFail();
    }

    /**
     * @return Collection<CaseLabel>
     */
    public function getByOrganisation(EloquentOrganisation $organisation): Collection
    {
        return $organisation->caseLabels()->get();
    }

    /**
     * @return Collection<CaseLabel>
     */
    public function getSelectableByOrganisation(EloquentOrganisation $organisation): Collection
    {
        return $organisation->caseLabels()->where('is_selectable', true)->get();
    }
}
