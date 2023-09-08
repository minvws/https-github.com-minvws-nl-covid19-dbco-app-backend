<?php

declare(strict_types=1);

use App\Models\CovidCase\Symptoms;
use App\Services\FragmentMigrationService;
use Illuminate\Database\Migrations\Migration;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class UpdateSymptomsConvertWasSymptomaticAtTimeOfCall extends Migration
{
    private function migrateCaseSymptoms(callable $callback, string $action = 'migrate'): void
    {
        /** @var FragmentMigrationService $fragmentMigrationService */
        $fragmentMigrationService = app(FragmentMigrationService::class);

        $caseFragments = [
            'symptoms',
        ];

        $fragmentMigrationService->covidCase($caseFragments)->updateWithProgress(
            static function (object $case, array $fragments) use ($callback) {
                if (!isset($fragments['symptoms'])) {
                    return false; // no fragment, so nothing to migrate
                }

                return $callback($case, $fragments['symptoms']);
            },
        );
    }

    public function up(): void
    {
        $this->migrateCaseSymptoms(static function (object $case, object $symptoms) {
            if (property_exists($symptoms, 'stillHadSymptomsAt')) {
                return false; // already migrated
            }

            $symptoms->stillHadSymptomsAt = null;

            if (!isset($symptoms->wasSymptomaticAtTimeOfCall) || $symptoms->wasSymptomaticAtTimeOfCall === null) {
                $symptoms->wasSymptomaticAtTimeOfCall = YesNoUnknown::unknown()->value;
                return true;
            }

            $symptoms->wasSymptomaticAtTimeOfCall = $symptoms->wasSymptomaticAtTimeOfCall
                ? YesNoUnknown::yes()->value
                : YesNoUnknown::no()->value;
            return true;
        });
    }

    public function down(): void
    {
        $this->migrateCaseSymptoms(static function (object $case, object $symptoms) {
            /** @var Symptoms $symptoms */
            if (!property_exists($symptoms, 'stillHadSymptomsAt')) {
                return false; // not migrated
            }

            unset($symptoms->stillHadSymptomsAt);

            switch ($symptoms->wasSymptomaticAtTimeOfCall) {
                case YesNoUnknown::yes()->value:
                    $symptoms->wasSymptomaticAtTimeOfCall = true;
                    break;
                case YesNoUnknown::no()->value:
                    $symptoms->wasSymptomaticAtTimeOfCall = false;
                    break;
                case YesNoUnknown::unknown()->value:
                default:
                    $symptoms->wasSymptomaticAtTimeOfCall = null;
                    break;
            }

            return true;
        }, 'rollback');
    }
}
