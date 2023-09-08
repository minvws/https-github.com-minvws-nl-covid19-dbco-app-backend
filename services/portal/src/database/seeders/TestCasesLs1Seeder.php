<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use Carbon\CarbonImmutable;
use Database\Factories\Eloquent\EloquentCaseFactory;
use Illuminate\Database\Seeder;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

use function ceil;

class TestCasesLs1Seeder extends Seeder
{
    public function run(int $totalCasesToCreate, int $chunkSize): void
    {
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output, $totalCasesToCreate);
        $progressBar->start();

        $organisationDemo = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_UUID)->firstOrFail();
        $organisationLs1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_OUTSOURCE_ORGANISATION_UUID)->firstOrFail();

        /** @var CaseList $caseList */
        $caseList = CaseList::where('organisation_uuid', $organisationLs1->uuid)
            ->where('is_queue', 1)
            ->where('is_default', 1)
            ->withoutGlobalScopes()
            ->firstOrFail();

        $chunks = (int) ceil($totalCasesToCreate / $chunkSize);
        for ($i = 1; $i <= $chunks; $i++) {
            $casesToCreate = $i === $chunks ? $totalCasesToCreate + $chunkSize - ($chunks * $chunkSize) : $chunkSize;

            /** @var EloquentCaseFactory $eloquentCaseFactory */
            $eloquentCaseFactory = EloquentCase::factory();
            $eloquentCaseFactory->count($casesToCreate)
                ->withFragments()
                ->create([
                    'assigned_case_list_uuid' => $caseList->uuid,
                    'organisation_uuid' => $organisationDemo->uuid,
                    'assigned_organisation_uuid' => $organisationLs1->uuid,
                    'bco_status' => BCOStatus::draft(),
                    'created_at' => CarbonImmutable::now(),
                ]);

            $progressBar->advance($casesToCreate);
        }

        $output->writeln('');
        $progressBar->finish();
    }
}
