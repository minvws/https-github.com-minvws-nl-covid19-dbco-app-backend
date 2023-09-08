<?php

declare(strict_types=1);

use App\Services\FragmentMigrationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Updates the structure of the vaccination fragments to support the input of unlimited vaccine injections.
 * The schema versions of the case/fragment are bumped after the patches are applied.
 */
class SupportUnlimitedVaccineInjections extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(
            function (): void {
                /** @var FragmentMigrationService $fragmentMigrationService */
                $fragmentMigrationService = app(FragmentMigrationService::class);
                $fragmentMigrationService->covidCase(['vaccination'])
                    ->update(
                        function (object $case, array &$fragments): void {
                            if (!isset($fragments['vaccination'])) {
                                return;
                            }

                            Log::info('Update vaccination fragment.', ['uuid' => $case->uuid, 'table' => 'covidcase']);
                            $this->updateVaccinationFragment($fragments['vaccination']);
                        },
                    );
                $fragmentMigrationService->task(['vaccination'])
                    ->update(
                        function (object $task, array &$fragments): void {
                            if (!isset($fragments['vaccination'])) {
                                return;
                            }

                            Log::info('Update vaccination fragment.', ['uuid' => $task->uuid, 'table' => 'task']);
                            $this->updateVaccinationFragment($fragments['vaccination']);
                        },
                    );
            },
        );
    }

    public function updateVaccinationFragment(object $vaccination): void
    {
        $vaccineInjections = $this->createVaccineInjections($vaccination);

        $vaccination->vaccineInjections = count($vaccineInjections) ? $vaccineInjections : null;
        $vaccination->hasCompletedVaccinationSeries = null;

        unset(
            $vaccination->dateInjectionFirst,
            $vaccination->dateInjectionSecond,
            $vaccination->vaccinationCount,
            $vaccination->vaccineType,
            $vaccination->otherVaccineType,
        );
    }

    /**
     * @return array<stdClass>
     */
    private function createVaccineInjections(object $vaccination): array
    {
        if (
            isset($vaccination->vaccineType) &&
            substr($vaccination->vaccineType, 0, 12) === 'heterologous'
        ) {
            return $this->createVaccineInjectionsFromHeterologousVaccineType($vaccination);
        }

        switch ($vaccination->vaccinationCount ?? 0) {
            case 1:
                return [
                    $this->createVaccineInjection(
                        $vaccination->dateInjectionFirst ?? null,
                        $vaccination->vaccineType ?? null,
                        $vaccination->otherVaccineType ?? null,
                    ),
                ];
            case 2:
                return [
                    $this->createVaccineInjection(
                        $vaccination->dateInjectionFirst ?? null,
                        $vaccination->vaccineType ?? null,
                        $vaccination->otherVaccineType ?? null,
                    ),
                    $this->createVaccineInjection(
                        $vaccination->dateInjectionSecond ?? null,
                        $vaccination->vaccineType ?? null,
                        $vaccination->otherVaccineType ?? null,
                    ),
                ];
            default:
                return [];
        }
    }

    /**
     * @return array<stdClass>
     */
    private function createVaccineInjectionsFromHeterologousVaccineType(object $vaccination): array
    {
        /** @var string $vaccineTypeWithoutPrefix */
        $vaccineTypeWithoutPrefix = str_replace('heterologous_', '', $vaccination->vaccineType);
        $extractedVaccineTypes = explode('_', $vaccineTypeWithoutPrefix);

        if (count($extractedVaccineTypes) !== 2) {
            throw new LogicException(
                sprintf(
                    'Expected the amount of heterologous vaccine types to be 2, got: %d. Source: %s',
                    count($extractedVaccineTypes),
                    $vaccination->vaccineType,
                ),
            );
        }

        return [
            $this->createVaccineInjection($vaccination->dateInjectionFirst ?? null, $extractedVaccineTypes[0], null),
            $this->createVaccineInjection($vaccination->dateInjectionSecond ?? null, $extractedVaccineTypes[1], null),
        ];
    }

    private function createVaccineInjection(
        ?string $injectionDate,
        ?string $vaccineType,
        ?string $otherVaccineType,
    ): stdClass {
        $vaccineInjection = new stdClass();
        $vaccineInjection->schemaVersion = 1;
        $vaccineInjection->injectionDate = $injectionDate;
        $vaccineInjection->isInjectionDateEstimated = null;
        $vaccineInjection->vaccineType = $vaccineType;
        $vaccineInjection->otherVaccineType = $otherVaccineType;

        return $vaccineInjection;
    }
}
