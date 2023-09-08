<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Database\Seeder;

class CaseLabelSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Note: The sortorder does currently not match the prod environment.
         * On the next label update, create a migration the adds the new label and resets the sort order. Then use that sortorder here.
         */
        $caseLabelsToCreate = [
            'not_identified' => ['sortorder' => 210, 'label' => 'Niet geïdentificeerd', 'is_selectable' => false],
            'healthcare' => ['sortorder' => 200, 'label' => 'Zorg', 'is_selectable' => true],
            'healthcare_residant' => ['sortorder' => 190, 'label' => 'Bewoner zorg', 'is_selectable' => true],
            'healthcare_employee' => ['sortorder' => 180, 'label' => 'Medewerker zorg', 'is_selectable' => true],
            'school' => ['sortorder' => 170, 'label' => 'School', 'is_selectable' => true],
            'contact_profession' => ['sortorder' => 160, 'label' => 'Contactberoep', 'is_selectable' => true],
            'social_institution' => ['sortorder' => 150, 'label' => 'Maatschappelijke instelling', 'is_selectable' => true],
            'shipping_person' => ['sortorder' => 140, 'label' => 'Scheepvaart opvarende', 'is_selectable' => true],
            'flights' => ['sortorder' => 130, 'label' => 'Vluchten', 'is_selectable' => true],
            'abroad' => ['sortorder' => 120, 'label' => 'Buitenland', 'is_selectable' => true],
            'voi_voc' => ['sortorder' => 110, 'label' => 'VOI/VOC', 'is_selectable' => true],
            'repeat_result' => ['sortorder' => 100, 'label' => 'Herhaaluitslag', 'is_selectable' => true],
            'external' => ['sortorder' => 90, 'label' => 'Buiten meldportaal/CoronIT', 'is_selectable' => true],
            'sample' => ['sortorder' => 80, 'label' => 'Steekproef', 'is_selectable' => true],
            'incomplete_data' => ['sortorder' => 70, 'label' => 'Onvolledige gegevens', 'is_selectable' => true],
            'index_unaware_result' => ['sortorder' => 60, 'label' => 'Index weet uitslag niet', 'is_selectable' => true],
            'outbreak' => ['sortorder' => 50, 'label' => 'Uitbraak', 'is_selectable' => true],
            'health_indication' => ['sortorder' => 40, 'label' => 'Gezondheidsindicatie', 'is_selectable' => true],
            'intake_submitted' => ['sortorder' => 30, 'label' => 'Intake ingevuld', 'is_selectable' => false],
            'intake_invited' => ['sortorder' => 20, 'label' => 'Uitgenodigd voor intake', 'is_selectable' => false],
            'osiris_notification_failed' => ['sortorder' => 10, 'label' => 'Osiris melding mislukt', 'is_selectable' => true],
        ];

        foreach ($caseLabelsToCreate as $caseLabelCode => $caseLabelAttributes) {
            $existingCaseLabel = CaseLabel::where('code', $caseLabelCode)->exists();

            if ($existingCaseLabel) {
                continue;
            }

            CaseLabel::factory()->create([
                'code' => $caseLabelCode,
                'label' => $caseLabelAttributes['label'],
                'is_selectable' => $caseLabelAttributes['is_selectable'],
            ]);
        }

        /** @var array<CaseLabel> $caseLabels */
        $caseLabels = CaseLabel::all();
        $organisationsUuids = EloquentOrganisation::pluck('uuid');

        foreach ($caseLabels as $caseLabel) {
            $sortorder = $caseLabelsToCreate[$caseLabel->code]['sortorder'];
            $caseLabel->organisations()->syncWithPivotValues($organisationsUuids, [
                'sortorder' => $sortorder,
            ]);
        }
    }
}
