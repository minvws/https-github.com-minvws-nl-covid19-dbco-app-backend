<?php

declare(strict_types=1);

use App\Services\FragmentMigrationService;
use Illuminate\Database\Migrations\Migration;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class UpdateBooleanYesNoUnknownFragments extends Migration
{
    public function up(): void
    {
        /** @var FragmentMigrationService $fragmentMigrationService */
        $fragmentMigrationService = app(FragmentMigrationService::class);

        $caseFragments = [
            'alternativeLanguage',
            'alternateContact',
            'alternateResidency',
            'groupTransport',
            'housemates',
            'pregnancy',
            'recentBirth',
            'sourceEnvironments',
        ];

        $fragmentMigrationService->covidCase($caseFragments)->update(function (object $case, array $fragments): void {
            $this->convertBooleanToYesNoUnknown($fragments, 'alternativeLanguage', 'useAlternativeLanguage');
            $this->convertBooleanToYesNoUnknown($fragments, 'alternateContact', 'hasAlternateContact');
            $this->convertBooleanToYesNoUnknown($fragments, 'alternateResidency', 'hasAlternateResidency');
            $this->convertBooleanToYesNoUnknown($fragments, 'groupTransport', 'withReservedSeats');
            $this->convertBooleanToYesNoUnknown($fragments, 'housemates', 'hasHousemates');
            $this->convertBooleanToYesNoUnknown($fragments, 'pregnancy', 'isPregnant');
            $this->convertBooleanToYesNoUnknown($fragments, 'recentBirth', 'hasRecentlyGivenBirth');
            $this->convertBooleanToYesNoUnknown($fragments, 'sourceEnvironments', 'hasLikelySourceEnvironments');
        });

        $taskFragments = [
            'alternativeLanguage',
            'alternateContact',
            'circumstances',
            'test',
        ];

        $fragmentMigrationService->task($taskFragments)->update(function (object $case, array $fragments): void {
            $this->convertBooleanToYesNoUnknown($fragments, 'alternativeLanguage', 'useAlternativeLanguage');
            $this->convertBooleanToYesNoUnknown($fragments, 'alternateContact', 'hasAlternateContact');
            $this->convertBooleanToYesNoUnknown($fragments, 'circumstances', 'wasUsingPPE');
            $this->convertBooleanToYesNoUnknown($fragments, 'test', 'isTested');
        });

        $contextFragments = [
            'circumstances',
            'contact',
        ];

        $fragmentMigrationService->context($contextFragments)->update(function (object $context, array $fragments): void {
            $this->convertBooleanToYesNoUnknown($fragments, 'circumstances', 'isUsingPPE');
            $this->convertBooleanToYesNoUnknown($fragments, 'circumstances', 'causeForConcern');
            $this->convertBooleanToYesNoUnknown($fragments, 'contact', 'notificationConsent');
        });
    }

    public function down(): void
    {
        // No migration needed to roll back
    }

    private function convertBooleanToYesNoUnknown(array $fragments, string $fragment, string $field): void
    {
        if (!isset($fragments[$fragment])) {
            return;
        }

        $fragmentObject = $fragments[$fragment];

        if (isset($fragmentObject->$field) && is_bool($fragmentObject->$field)) {
            $fragmentObject->$field = $fragmentObject->$field ? YesNoUnknown::yes()->value : YesNoUnknown::no()->value;
        }
    }
}
