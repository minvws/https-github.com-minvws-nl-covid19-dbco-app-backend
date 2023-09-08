<?php

declare(strict_types=1);

use App\Services\FragmentMigrationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class UpdateHasHouseMatesYesNoUnknown extends Migration
{
    public function up(): void
    {
        /** @var FragmentMigrationService $fragmentMigrationService */
        $fragmentMigrationService = app(FragmentMigrationService::class);

        $fragmentMigrationService->covidCase(['housemates'])->update(static function (object $case, array $fragments): void {
            if (!isset($fragments['housemates'])) {
                return;
            }

            Log::info('Migration update covidcase.housemates fragment: Case uuid: ' . $case->uuid);
            if (isset($fragments['housemates']->hasHouseMates) && is_bool($fragments['housemates']->hasHouseMates)) {
                Log::info(
                    'Migration update covidcase.housemates fragment: Case uuid: ' . $case->uuid . ', Found boolean hasHouseMates...updating fragment. Memory: ' . memory_get_usage(),
                );
                $fragments['housemates']->hasHouseMates = $fragments['housemates']->hasHouseMates
                    ? YesNoUnknown::yes()->value
                    : YesNoUnknown::no()->value;
            } else {
                Log::info(
                    'Migration update covidcase.housemates fragment: Case uuid: ' . $case->uuid . ', Update not needed. Memory: ' . memory_get_usage(),
                );
            }
        });
    }

    public function down(): void
    {
        // nothing to do here
    }
}
