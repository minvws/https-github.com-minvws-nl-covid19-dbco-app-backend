<?php

declare(strict_types=1);

use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration will only have effect on dev/test/acc, because labels are not seeded yet on prod.
 */
class UpdateLabels extends Migration
{
    public function up(): void
    {
        DB::update("UPDATE case_label SET label = 'Index weet uitslag niet' WHERE label = 'Index weet uitslag'");
        DB::update("UPDATE case_label SET label = 'Buiten meldportaal/CoronIT' WHERE label = 'Buiten meldportaal'");

        $this->addVoiVoc();
    }

    public function down(): void
    {
        // cannot be reverted
    }

    public function addVoiVoc(): void
    {
        /** @var CaseLabel|null $buitenland */
        $buitenland = CaseLabel::where('label', 'Buitenland')->first();
        if ($buitenland === null) {
            // Labels not seeded, no update needed.
            return;
        }

        $buitenlandOrder = DB::table('case_label_organisation')->where('case_label_uuid', $buitenland->uuid)->first();
        if ($buitenlandOrder === null) {
            return;
        }

        $newLabel = 'VOI/VOC';

        $caseLabel = CaseLabel::factory()->create([
            'label' => $newLabel,
        ]);

        $organisations = EloquentOrganisation::all();
        foreach ($organisations as $organisation) {
            $organisation->caseLabels()->attach($caseLabel, [
                'sortorder' => $buitenlandOrder->sortorder - 5,
            ]);
        }
    }
}
