<?php

declare(strict_types=1);

namespace App\Repositories\Dossier;

use App\Models\Disease\DiseaseModel;
use App\Models\Dossier\Dossier;
use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Support\Facades\DB;

class DossierRepository
{
    public function getDossier(int $id): ?Dossier
    {
        return Dossier::query()->find($id);
    }

    public function makeDossier(DiseaseModel $diseaseModel, EloquentOrganisation $organisation): Dossier
    {
        $dossier = new Dossier();
        $dossier->diseaseModel()->associate($diseaseModel);
        $dossier->organisation()->associate($organisation);
        return $dossier;
    }

    public function saveDossier(Dossier $dossier): void
    {
        DB::transaction(static function () use ($dossier): void {
            $dossier->save();
        });
    }
}
