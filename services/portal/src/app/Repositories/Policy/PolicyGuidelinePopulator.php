<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

/**
 * @phpstan-import-type PolicyGuidelineAttributes from PolicyGuideline
 */
class PolicyGuidelinePopulator
{
    public function __construct(
        private readonly Connection $db,
        private readonly PolicyGuidelineRepository $policyGuidelineRepository,
    )
    {
    }

    /**
     * @return Collection<PolicyGuideline>
     */
    public function populate(PolicyVersion $policyVersion): Collection
    {
        return $this->db->transaction(fn (): Collection
            => $this->policyGuidelineRepository->upsertPolicyGuidelinesByPolicyVersion($policyVersion, $this->getPolicyGuidelineData()));
    }

    /**
     * @return array<PolicyGuidelineAttributes>
     */
    private function getPolicyGuidelineData(): array
    {
        return [
            [
                'person_type' => PolicyPersonType::index(),
                'name' => 'Symptomatisch - Standaard',
                'identifier' => 'symptomatic',
                'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'source_start_date_addition' => -14,
                'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'source_end_date_addition' => -2,
                'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'contagious_start_date_addition' => -2,
                'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'contagious_end_date_addition' => 5,
                'sort_order' => 10,
            ],
            [
                'person_type' => PolicyPersonType::index(),
                'name' => 'Symptomatisch - Verlengd',
                'identifier' => 'symptomatic_extended',
                'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'source_start_date_addition' => -14,
                'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'source_end_date_addition' => -2,
                'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'contagious_start_date_addition' => -2,
                'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'contagious_end_date_addition' => 10,
                'sort_order' => 20,
            ],
            [
                'person_type' => PolicyPersonType::index(),
                'name' => 'Asymptomatisch - Standaard',
                'identifier' => 'asymptomatic',
                'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfTest(),
                'source_start_date_addition' => -14,
                'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfTest(),
                'source_end_date_addition' => -1,
                'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfTest(),
                'contagious_start_date_addition' => 0,
                'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfTest(),
                'contagious_end_date_addition' => 5,
                'sort_order' => 30,
            ],
            [
                'person_type' => PolicyPersonType::index(),
                'name' => 'Asymptomatisch - Verlengd',
                'identifier' => 'asymptomatic_extended',
                'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfTest(),
                'source_start_date_addition' => -14,
                'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfTest(),
                'source_end_date_addition' => -1,
                'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfTest(),
                'contagious_start_date_addition' => 0,
                'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfTest(),
                'contagious_end_date_addition' => 10,
                'sort_order' => 40,
            ],
            [
                'person_type' => PolicyPersonType::contact(),
                'name' => 'Quarantine standaard',
                'identifier' => 'quarantine',
                'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'source_start_date_addition' => -14,
                'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'source_end_date_addition' => -2,
                'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'contagious_start_date_addition' => -2,
                'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'contagious_end_date_addition' => 5,
                'sort_order' => 10,
            ],
            [
                'person_type' => PolicyPersonType::contact(),
                'name' => 'Geen quarantine',
                'identifier' => 'no_quarantine',
                'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'source_start_date_addition' => -14,
                'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'source_end_date_addition' => -2,
                'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'contagious_start_date_addition' => -2,
                'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                'contagious_end_date_addition' => 5,
                'sort_order' => 10,
            ],
        ];
    }
}
