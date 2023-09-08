<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Disease;

use App\Models\Disease\DiseaseModel;
use App\Models\Disease\VersionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

use function base_path;
use function file_get_contents;

class DiseaseModelFactory extends Factory
{
    protected $model = DiseaseModel::class;

    public function definition(): array
    {
        return [
            'status' => VersionStatus::Draft,
            'version' => 1,
            'shared_defs' => '[]',
            'dossier_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/dossier.schema.json')),
            'contact_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/contact.schema.json')),
            'event_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/event.schema.json')),
        ];
    }
}
