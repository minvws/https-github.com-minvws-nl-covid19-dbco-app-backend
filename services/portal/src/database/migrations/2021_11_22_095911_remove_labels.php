<?php

declare(strict_types=1);

use App\Models\Eloquent\CaseLabel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveLabels extends Migration
{
    public function up(): void
    {
        $caseLabelsUuidsToBeRemoved = CaseLabel::query()
            ->whereIn('label', [
                'Bewoner detentiecentrum',
                'Bewoner AZC',
                'Dak thuisloos',
            ])
            ->pluck('uuid');

        DB::table('case_case_label')
            ->whereIn('case_label_uuid', $caseLabelsUuidsToBeRemoved)
            ->delete();
        DB::table('case_label_organisation')
            ->whereIn('case_label_uuid', $caseLabelsUuidsToBeRemoved)
            ->delete();
        DB::table('case_label')
            ->whereIn('uuid', $caseLabelsUuidsToBeRemoved)
            ->delete();
    }

    public function down(): void
    {
        // cannot be reverted
    }
}
