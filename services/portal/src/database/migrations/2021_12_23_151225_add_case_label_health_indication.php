<?php

declare(strict_types=1);

use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;

class AddCaseLabelHealthIndication extends Migration
{
    public function up(): void
    {
        $caseCount = CaseLabel::count();

        if ($caseCount <= 0) {
            return;
        }

        $caseLabel = CaseLabel::where(['code' => 'health_indication'])->first();

        if ($caseLabel === null) {
            $caseLabel = new CaseLabel();
            $caseLabel->uuid = Uuid::uuid4();
            $caseLabel->code = 'health_indication';
            $caseLabel->label = 'Gezondheidsindicatie';
            $caseLabel->save();
        }

        $organisations = EloquentOrganisation::all()->pluck('uuid');

        $caseLabel->organisations()->syncWithPivotValues($organisations, ['sortorder' => 160]);
    }

    public function down(): void
    {
        CaseLabel::where(['code' => 'health_indication'])->delete();
    }
}
