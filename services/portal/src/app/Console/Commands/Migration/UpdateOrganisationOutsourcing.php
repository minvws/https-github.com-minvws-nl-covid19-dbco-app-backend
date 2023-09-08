<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Console\Command;

use function collect;
use function sprintf;

class UpdateOrganisationOutsourcing extends Command
{
    private array $additions = [
        'majorel' => ['17003'],
        'riff' => ['16003', '17003', '24003'],
        '99004' => ['11003', '18003'],
        'yource' => ['05003', '07003'],
    ];

    private array $removals = [
        '99001' => ['05003', '17003'],
        '99002' => ['08003', '16003'],
        'riff' => ['07003'],
        '99003' => ['02003', '03003'],
        'teleperformance' => ['07003', '23003'],
        '99006' => ['06003'],
        'yource' => ['21003'],
    ];

    protected $signature = 'migration:update-organisation-outsourcing {--force}';

    protected $description = 'Update the available outsourcing partners for the ggd regions';

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
        $this->handledRemovals();

        $this->getOutput()->success('Finished updating outsource mapping.');

        return Command::SUCCESS;
    }

    private function check(): array
    {
        $result = [];
        foreach ($this->additions as $outsourceOrgId => $ggdOrgIds) {
            /** @var EloquentOrganisation $outsourceOrg */
            $outsourceOrg = EloquentOrganisation::byExternalId((string) $outsourceOrgId)->sole();
            foreach ($ggdOrgIds as $ggdOrgId) {
                /** @var EloquentOrganisation $ggdOrg */
                $ggdOrg = EloquentOrganisation::byExternalId($ggdOrgId)->sole();
                if (!$ggdOrg->outsourceOrganisations->contains($outsourceOrg->uuid)) {
                    continue;
                }

                $result[] = sprintf('Outsource org %s is already linked to %s.', $outsourceOrg->name, $ggdOrg->name);
            }
            $outsourceOrg->save();
        }

        foreach ($this->removals as $outsourceOrgId => $ggdOrgIds) {
            /** @var EloquentOrganisation $outsourceOrg */
            $outsourceOrg = EloquentOrganisation::byExternalId((string) $outsourceOrgId)->with('outsourceOrganisations')->sole();
            foreach ($ggdOrgIds as $ggdOrgId) {
                /** @var EloquentOrganisation $ggdOrg */
                $ggdOrg = EloquentOrganisation::byExternalId($ggdOrgId)->sole();
                if (!$ggdOrg->outsourceOrganisations->contains($outsourceOrg->uuid)) {
                    $result[] = sprintf('Outsource org %s is not linked to %s.', $outsourceOrg->name, $ggdOrg->name);
                }
            }
            $outsourceOrg->save();
        }
        return $result;
    }

    private function handleAdditions(): void
    {
        foreach ($this->additions as $outsourceOrgId => $ggdOrgIds) {
            /** @var EloquentOrganisation $outsourceOrg */
            $outsourceOrg = EloquentOrganisation::byExternalId((string) $outsourceOrgId)->sole();
            foreach ($ggdOrgIds as $ggdOrgId) {
                $ggdOrganisation = EloquentOrganisation::byExternalId($ggdOrgId)->sole();
                if (!$ggdOrganisation->outsourceOrganisations->contains($outsourceOrg->uuid)) {
                    $ggdOrganisation->outsourceOrganisations()->attach($outsourceOrg);
                }
            }
            $outsourceOrg->save();
        }
    }

    private function handledRemovals(): void
    {
        foreach ($this->removals as $outsourceOrgId => $ggdOrgIds) {
            /** @var EloquentOrganisation $outsourceOrg */
            $outsourceOrg = EloquentOrganisation::byExternalId((string) $outsourceOrgId)->sole();
            foreach ($ggdOrgIds as $ggdOrgId) {
                $ggdOrganisation = EloquentOrganisation::byExternalId($ggdOrgId)->sole();
                $ggdOrganisation->outsourceOrganisations()->detach($outsourceOrg);
            }
            $outsourceOrg->save();
        }
    }
}
