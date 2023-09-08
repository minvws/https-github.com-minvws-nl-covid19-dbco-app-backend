<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Console\Command;

class UpdateOrganisationOutsourcing4 extends Command
{
    private const UPDATES = [
        '18003' => ['17003'],
        '17003' => ['18003'],
        '25003' => ['25003'],
        '13003' => ['13003'],
        '21003' => ['20003', '24003'],
        '23003' => ['20003', '24003'],
        '19003' => ['20003', '24003'],
        '22003' => ['20003', '24003'],
    ];

    protected $signature = 'migration:update-organisation-outsourcing-4';

    protected $description = 'Update the GGD outsourcing partners for the GGD regions (DBCO-4173)';

    public function handle(): int
    {
        $this->handleUpdates();
        $this->getOutput()->success('Finished updating outsource mapping.');

        return Command::SUCCESS;
    }

    private function handleUpdates(): void
    {
        foreach (self::UPDATES as $organisationId => $outsourceOrganisationIds) {
            /** @var EloquentOrganisation $organisation */
            $organisation = EloquentOrganisation::byExternalId((string) $organisationId)->sole();
            foreach ($outsourceOrganisationIds as $outsourceOrganisationId) {
                /** @var EloquentOrganisation $outsourceOrganisation */
                $outsourceOrganisation = EloquentOrganisation::byExternalId((string) $outsourceOrganisationId)->sole();
                $outsourceOrganisation->has_outsource_toggle = true;
                $outsourceOrganisation->save();

                if (!$organisation->outsourceOrganisations->contains($outsourceOrganisation->uuid)) {
                    $organisation->outsourceOrganisations()->attach($outsourceOrganisation);
                }
            }
        }
    }
}
