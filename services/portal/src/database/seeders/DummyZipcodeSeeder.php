<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;

use function range;

final class DummyZipcodeSeeder extends Seeder
{
    public function run(): void
    {
        $output = new ConsoleOutput();

        $ggd1Organisation = EloquentOrganisation::find(DummySeeder::DEMO_ORGANISATION_UUID);
        if ($ggd1Organisation instanceof EloquentOrganisation) {
            //Unused range in Noord-Holland
            $this->insertZipcodesForOrganisation($ggd1Organisation, 1290);
        } else {
            $output->writeln('GGD1 organisation not found. Please run DummySeeder first.');
        }

        $ggd2Organisation = EloquentOrganisation::find(DummySeeder::DEMO_ORGANISATION_TWO_UUID);
        if ($ggd2Organisation instanceof EloquentOrganisation) {
            //Unused range in Limburg
            $this->insertZipcodesForOrganisation($ggd2Organisation, 6490);
        } else {
            $output->writeln('GGD2 organisation not found. Please run DummySeeder first.');
        }
    }

    /**
     * Creates zipcodes from $numberRangeStart + 10 and all char options, creating 6760 zipcodes.
     */
    private function insertZipcodesForOrganisation(EloquentOrganisation $organisation, int $numberRangeStart): void
    {
        $rows = [];

        for ($zipcodeNumbers = $numberRangeStart; $zipcodeNumbers < $numberRangeStart + 10; $zipcodeNumbers++) {
            foreach (range('A', 'Z') as $firstChar) {
                foreach (range('A', 'Z') as $secondChar) {
                    $rows[] = [
                        'zipcode' => $zipcodeNumbers . $firstChar . $secondChar,
                        'organisation_uuid' => $organisation->uuid,
                    ];
                }
            }
        }

        DB::table('zipcode')->insert($rows);
    }
}
