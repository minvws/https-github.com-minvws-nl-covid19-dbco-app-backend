<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

class OrganisationOutsourceTable extends Command
{
    protected $signature = 'organisation:outsource-table';

    protected $description = 'Print the current organisation outsourcing mapping';

    public function handle(): int
    {
        $organisations = EloquentOrganisation::where('type', OrganisationType::regionalGGD()->value)
            ->orderBy('external_id')
            ->get();

        $outsourceOrganisations = EloquentOrganisation::where('type', OrganisationType::outsourceOrganisation()->value)
            ->orderBy('name')
            ->get();

        $outsourceDepartments = EloquentOrganisation::where('type', OrganisationType::outsourceDepartment()->value)
            ->orderBy('name')
            ->get();

        $table = new Table($this->getOutput());
        $table->setHeaders([
            'VRregio',
            'HPZone code',
            'GGD',
            ...$outsourceOrganisations->map(static fn($org) => $org->name . ' O'),
            ...$outsourceDepartments->map(static fn($org) => $org->name . ' D'),
        ]);

        $table->addRow([
            '',
            '',
            '',
            ...$outsourceOrganisations->map(static fn($org) => $org->externalId),
            ...$outsourceDepartments->map(static fn($org) => $org->externalId),
        ]);

        /** @var EloquentOrganisation $organisation */
        foreach ($organisations as $organisation) {
            $table->addRow([
                $organisation->external_id,
                $organisation->hpZoneCode,
                $organisation->name,
                ...$outsourceOrganisations->map(
                    static fn(EloquentOrganisation $org) => $organisation->outsourceOrganisations->contains($org->uuid) ? 'X' : ''
                ),
                ...$outsourceDepartments->map(
                    static fn(EloquentOrganisation $org) => $organisation->outsourceOrganisations->contains($org->uuid) ? 'X' : ''
                ),
            ]);
        }

        $table->render();


        return Command::SUCCESS;
    }
}
