<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Console\Command;

class UpdateOrganisationOutsourcing3 extends Command
{
    private const UPDATES = [
        '13003' => ['99004', 'yource'],
        '25003' => ['yource'],
        '14003' => ['yource'],
        '10003' => ['99004', 'webhelp'],
        '12003' => ['99004', 'webhelp'],
        '11003' => ['webhelp'],
        '03003' => ['webhelp'],
        '01003' => ['yource'],
        '02003' => ['yource'],
        '15003' => ['majorel'],
        '16003' => ['riff'],
        '17003' => ['riff', 'majorel'],
        '18003' => ['webhelp', '99004'],
        '07003' => ['webhelp', 'yource'],
        '08003' => ['riff', 'yource'],
        '04003' => ['riff'],
        '06003' => ['riff'],
        '05003' => ['99004', 'riff'],
        '09003' => [],
        '21003' => ['majorel'],
        '23003' => ['riff'],
        '24003' => ['webhelp'],
        '20003' => ['yource'],
        '22003' => ['riff'],
        '19003' => ['yource'],
    ];

    protected $signature = 'migration:update-organisation-outsourcing-3';

    protected $description = 'Update the available outsourcing partners for the GGD regions (release 1.10)';

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
            $organisation->outsourceOrganisations()->detach();
            foreach ($outsourceOrganisationIds as $outsourceOrganisationId) {
                /** @var EloquentOrganisation $outsourceOrganisation */
                $outsourceOrganisation = EloquentOrganisation::byExternalId((string) $outsourceOrganisationId)->sole();
                $organisation->outsourceOrganisations()->attach($outsourceOrganisation);
            }
        }
    }
}
