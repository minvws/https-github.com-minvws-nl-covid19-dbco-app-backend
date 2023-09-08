<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Dossier;

use App\Models\Dossier\Dossier;
use App\Services\AuthenticationService;
use App\Services\BcoNumber\BcoNumberService;
use Illuminate\Database\Eloquent\Factories\Factory;

use function app;

class DossierFactory extends Factory
{
    protected $model = Dossier::class;

    public function definition(): array
    {
        $identifier = app(BcoNumberService::class)->makeUniqueNumber()->bco_number;
        $organisationUuid = app(AuthenticationService::class)->getRequiredSelectedOrganisation()->uuid;
        return [
            'identifier' => $identifier,
            'organisation_uuid' => $organisationUuid,
        ];
    }
}
