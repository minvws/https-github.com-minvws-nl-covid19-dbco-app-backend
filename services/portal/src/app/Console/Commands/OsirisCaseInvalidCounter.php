<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Traits\WithTypedInput;
use App\Repositories\CaseRepository;
use App\Services\Osiris\CaseValidator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use function assert;
use function sprintf;

class OsirisCaseInvalidCounter extends Command
{
    use WithTypedInput;

    /** @var string $signature */
    protected $signature = 'osiris:case-invalid-counter
        {start-date : The start-date for the period in which the cases are created (format: d-m-Y)}
        {end-date : The end-date for the period in which the cases are created (this day included, format: d-m-Y)}
        {--chunk-size=100 : Chunk-size}
    ';

    protected $description = 'Count the cases (between dates) that are invalid for Osiris';

    public function handle(CaseRepository $caseRepository, CaseValidator $caseValidator): void
    {
        $endDate = $this->getStringArgument('end-date');
        $startDate = $this->getStringArgument('start-date');
        $chunkSize = $this->getIntegerOption('chunk-size');

        $counterTotalCases = 0;
        $counterValidCases = 0;
        $counterInvalidCases = 0;
        $validationErrors = new Collection();

        $carbonStartDate = CarbonImmutable::createFromFormat('d-m-Y', $startDate);
        assert($carbonStartDate instanceof CarbonImmutable);
        $carbonEndDate = CarbonImmutable::createFromFormat('d-m-Y', $endDate);
        assert($carbonEndDate instanceof CarbonImmutable);

        $progressBar = $this->output->createProgressBar();
        $progressBar->start();

        $caseRepository->chunkCasesBetweenDates(
            $carbonStartDate->floorDay(),
            $carbonEndDate->ceilDay(),
            Model::CREATED_AT,
            $chunkSize,
            static function (Collection $cases) use (
                $caseValidator,
                &$progressBar,
                &$counterTotalCases,
                &$counterValidCases,
                &$counterInvalidCases,
                &$validationErrors,
                $chunkSize,
            ): void {
                foreach ($cases as $case) {
                    $counterTotalCases++;

                    $caseValidationErrors = $caseValidator->validate($case);
                    if ($caseValidationErrors->isEmpty()) {
                        $counterValidCases++;
                    } else {
                        $counterInvalidCases++;
                        $validationErrors = $validationErrors->merge($caseValidationErrors->all());
                    }
                }

                $progressBar->advance($chunkSize);
            },
        );

        $progressBar->finish();

        $this->newLine();

        $this->info(sprintf('total cases: %s', $counterTotalCases));
        $this->info(sprintf('valid cases: %s', $counterValidCases));
        $this->info(sprintf('invalid cases: %s', $counterInvalidCases));
        $validationErrors = $validationErrors
            ->countBy()
            ->each(function (int $count, string $error): void {
                $this->info(sprintf('- %s: %s', $error, $count));
            });
    }
}
