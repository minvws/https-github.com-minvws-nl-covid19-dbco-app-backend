<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Intake;
use Exception;
use Illuminate\Database\Seeder;
use Tests\ModelCreator;

use function rand;

class IntakeSeeder extends Seeder
{
    use ModelCreator;

    /**
     * @throws Exception
     */
    public function run(int $createCount = 20): void
    {
        $organisation = EloquentOrganisation::find(DummySeeder::DEMO_ORGANISATION_UUID);

        if (!$organisation instanceof EloquentOrganisation) {
            throw new Exception('Demo organisation not found');
        }

        for ($i = 0; $i < $createCount; $i++) {
            $intake = $this->createIntakeForOrganisation($organisation);
            $this->addIntakeFragmentsToIntake($intake);
            $this->addRandomLabels($intake);
        }
    }

    private function addRandomLabels(Intake $intake): void
    {
        $labels = CaseLabel::query()->get()->random(rand(0, 2));
        foreach ($labels as $label) {
            $intake->caseLabels()->attach($label);
        }
    }
}
