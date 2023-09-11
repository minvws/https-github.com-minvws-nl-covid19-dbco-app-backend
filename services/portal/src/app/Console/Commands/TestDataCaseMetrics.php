<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Eloquent\CaseStatusHistory;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use Carbon\CarbonImmutable;
use Closure;
use Exception;
use Faker;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\BCOStatus;

use function count;
use function is_string;
use function random_int;
use function sprintf;

class TestDataCaseMetrics extends Command
{
    protected $signature = 'test-data:case-metrics {--caseSeedCount=0} {--organisationUuid=00000000-0000-0000-0000-000000000000}';

    protected $description = 'Generate test data optimised for case metrics';

    private ?int $caseSeedCount = null;
    private ?EloquentOrganisation $organisation = null;

    /**
     * @throws Exception
     *
     * @codeCoverageIgnore
     */
    public function handle(): int
    {
        $this->seedCases();

        $caseUuids = DB::select("select uuid from covidcase");
        $caseCount = count($caseUuids);

        $this->output->writeln(sprintf('Augmenting %d cases', $caseCount));
        $bar = $this->output->createProgressBar($caseCount);
        $bar->setRedrawFrequency(1);
        $bar->start();

        foreach ($caseUuids as $caseUuid) {
            /** @var EloquentCase $case */
            $case = EloquentCase::where('uuid', $caseUuid->uuid)->first();
            $this->augmentCaseData($case, static function () use ($bar): void {
                $bar->advance();
            });
        }

        $this->output->writeln('');

        return self::SUCCESS;
    }

    /**
     * @throws Exception
     *
     * @codeCoverageIgnore
     */
    private function seedCases(): void
    {
        $count = $this->getCaseSeedCount();
        if ($count === 0) {
            $this->output->writeln("Skipping case seed");
            return;
        }

        $this->output->writeln(sprintf('Seeding %d cases', $count));

        $bar = $this->output->createProgressBar($count);
        $bar->setRedrawFrequency(1);
        $bar->start();

        $faker = Faker\Factory::create();
        for ($i = 0; $i < $count; $i++) {
            /** @var EloquentCase $case */
            $case = EloquentCase::factory()->create([
                'case_id' => sprintf("%'.07d", $i),
                'created_at' => CarbonImmutable::now()->subDays(random_int(0, 21)),
                'organisation_uuid' => $this->getOrganisation()->uuid,
                'bco_phase' => $this->getOrganisation()->bco_phase,
            ]);
            $case->index->firstname = $faker->firstName();
            $case->index->lastname = $faker->lastName();
            $case->contact->phone = $faker->phoneNumber();
            $case->contact->email = $faker->email();
            $case->save();

            $bar->advance();
        }

        $this->output->writeln('');
    }

    /**
     * @throws Exception
     *
     * @codeCoverageIgnore
     */
    private function augmentCaseData(EloquentCase $case, Closure $after): void
    {
        $changedAtSubDays = random_int(0, 200);
        if ($changedAtSubDays < 21) {
            $case->bco_status = BCOStatus::archived();
            $case->saveQuietly();

            CaseStatusHistory::query()->where('covidcase_uuid', '=', $case->uuid)
                ->update(['changed_at' => CarbonImmutable::now()->subDays($changedAtSubDays)->subDay()]);

            CaseStatusHistory::factory()
                ->for($case, 'case')
                ->state([
                    'bco_status' => BCOStatus::archived(),
                    'changed_at' => CarbonImmutable::now()->subDays($changedAtSubDays),
                ])
                ->createOneQuietly();
        }

        $after($case);
    }

    /**
     * @codeCoverageIgnore
     */
    private function getCaseSeedCount(): int
    {
        if ($this->caseSeedCount === null) {
            $this->caseSeedCount = (int) $this->option('caseSeedCount');
        }

        return $this->caseSeedCount;
    }

    /**
     * @throws ModelNotFoundException
     *
     * @codeCoverageIgnore
     */
    private function getOrganisation(): EloquentOrganisation
    {
        if ($this->organisation === null) {
            $organisationUuidArg = $this->option('organisationUuid');
            $organisationUuid = !empty($organisationUuidArg) && is_string($organisationUuidArg)
                ? $organisationUuidArg
                : '00000000-0000-0000-0000-000000000000';
            $this->organisation = EloquentOrganisation::where('uuid', $organisationUuid)->firstOrFail();
        }

        return $this->organisation;
    }
}
