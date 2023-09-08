<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Console\Command;

class UpdateOrganisationOutsourcing6 extends Command
{
    private const UPDATES = [
        '15003' => ['16003', '17003', '18003'],
        '16003' => ['15003', '17003', '18003'],
        '17003' => ['15003', '16003', '18003'],
        '18003' => ['15003', '16003', '17003'],
    ];

    protected $signature = 'migration:update-organisation-outsourcing-6';

    protected $description = 'Update the GGD outsourcing partners for the GGD regions (DBCO-4662)';

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
