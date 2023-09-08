<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Console\Command;
use Illuminate\Database\RecordsNotFoundException;

use function collect;
use function sprintf;

class UpdateOrganisationOutsourcing2 extends Command
{
    private const ADDITIONS = [
        '03003' => ['99004', 'yource'], // GGD Drenthe can outsource to SOS/CED
        '20003' => ['webhelp', 'yource'], // GGD West Brabant can outsource to webhelp
    ];

    /*
     * Remove the following national partners:
     */
    private const REMOVALS = [
        99_001, // ANWB
        99_002, // Eurocross
        99_003, // Rode Kruis
        99_006, // VHD
    ];

    protected $signature = 'migration:update-organisation-outsourcing-2 {--force}';

    protected $description = 'Update the available outsourcing partners for the ggd regions (acceptance)';

    public function handle(): int
    {
        $errors = $this->check();
        if (!empty($errors)) {
            collect($errors)->each(fn(string $error) => $this->getOutput()->error($error));
            $this->getOutput()->error('There were errors. Unexpected state, aborting migration. Has it already been ran?');

            if ((bool) $this->option('force') === false) {
                return Command::FAILURE;
            }
            $this->getOutput()->error('Forcing, continuing anyway...');
        }

        $this->handleAdditions();
        $this->handleRemovals();

        $this->getOutput()->success('Finished updating outsource mapping.');

        return Command::SUCCESS;
    }

    private function check(): array
    {
        $result = [];
        foreach (self::ADDITIONS as $organisationId => $outsourceOrganisationIds) {
            /** @var EloquentOrganisation $organisation */
            $organisation = EloquentOrganisation::byExternalId((string) $organisationId)->sole();
            foreach ($outsourceOrganisationIds as $outsourceOrganisationId) {
                /** @var EloquentOrganisation $outsourceOrganisation */
                $outsourceOrganisation = EloquentOrganisation::byExternalId((string) $outsourceOrganisationId)->sole();
                if (!$organisation->outsourceOrganisations->contains($outsourceOrganisation->uuid)) {
                    continue;
                }

                $result[] = sprintf('Outsource org %s is already linked to %s.', $outsourceOrganisation->name, $organisation->name);
            }
        }

        return $result;
    }

    private function handleAdditions(): void
    {
        foreach (self::ADDITIONS as $organisationId => $outsourceOrganisationIds) {
            /** @var EloquentOrganisation $organisation */
            $organisation = EloquentOrganisation::byExternalId((string) $organisationId)->sole();
            foreach ($outsourceOrganisationIds as $outsourceOrganisationId) {
                /** @var EloquentOrganisation $outsourceOrganisation */
                $outsourceOrganisation = EloquentOrganisation::byExternalId((string) $outsourceOrganisationId)->sole();
                if (!$organisation->outsourceOrganisations->contains($outsourceOrganisation->uuid)) {
                    $organisation->outsourceOrganisations()->attach($outsourceOrganisation);
                }
            }
            $organisation->save();
        }
    }

    private function handleRemovals(): void
    {
        foreach (self::REMOVALS as $outsourceOrganisationId) {
            try {
                /** @var EloquentOrganisation $outsourceOrganisation */
                $outsourceOrganisation = EloquentOrganisation::byExternalId((string) $outsourceOrganisationId)->sole();
                $organisations = EloquentOrganisation::whereHas(
                    'outsourceOrganisations',
                    static function ($query) use ($outsourceOrganisation): void {
                        $query->where('uuid', $outsourceOrganisation->uuid);
                    },
                )->get();

                foreach ($organisations as $organisation) {
                    $organisation->outsourceOrganisations()->detach([$outsourceOrganisation->uuid]);
                }
            } catch (RecordsNotFoundException $e) {
                $this->getOutput()->warning("No outsource organisation found with external id: {$outsourceOrganisationId}");
            }
        }
    }
}
