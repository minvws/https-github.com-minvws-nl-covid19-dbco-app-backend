<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Disease;

use App\Models\Disease\DiseaseModelUI;
use App\Models\Disease\VersionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiseaseModelUIFactory extends Factory
{
    protected $model = DiseaseModelUI::class;

    public function definition(): array
    {
        return [
            'status' => VersionStatus::Draft,
            'version' => 1,
            'dossier_schema' => '{}',
            'contact_schema' => '{}',
            'event_schema' => '{}',
            'translations' => '{}',
        ];
    }
}
