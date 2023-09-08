<?php

declare(strict_types=1);

use App\Services\FragmentMigrationService;
use Illuminate\Database\Migrations\Migration;

class MoveCallRemarksToCommunicationParticularities extends Migration
{
    public function up(): void
    {
        /** @var FragmentMigrationService $fragmentMigrationService */
        $fragmentMigrationService = app(FragmentMigrationService::class);

        $fragmentMigrationService->covidCase(['call', 'communication'])->update(static function (object $case, array &$fragments): void {
            if (!array_key_exists('call', $fragments)) {
                return;
            }

            if (!is_object($fragments['call'])) {
                return;
            }

            if (!property_exists($fragments['call'], 'remarks')) {
                return;
            }

            if (!isset($fragments['communication'])) {
                $fragments['communication'] = new stdClass();
            }

            $fragments['communication']->particularities = $fragments['call']->remarks;
        });
    }

    public function down(): void
    {
        // No migration needed to roll back
    }
}
