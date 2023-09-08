<?php

declare(strict_types=1);

namespace App\Repositories\Disease;

use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\VersionStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiseaseModelRepository
{
    public function getAllDiseaseModels(Disease $disease): Collection
    {
        return $disease->models()->orderBy('version', 'desc')->get();
    }

    public function getDiseaseModel(int $id): ?DiseaseModel
    {
        return DiseaseModel::query()->find($id);
    }

    public function firstDiseaseModelWithStatus(Disease $disease, VersionStatus $status): ?DiseaseModel
    {
        return $disease->models()->where('status', '=', $status)->with('disease')->first();
    }

    public function firstDiseaseModelWithVersion(Disease $disease, int $version): ?DiseaseModel
    {
        return $disease->models()->where('version', '=', $version)->with('disease')->first();
    }

    public function makeDiseaseModel(Disease $disease): DiseaseModel
    {
        $diseaseModel = new DiseaseModel();
        $diseaseModel->disease()->associate($disease);
        return $diseaseModel;
    }

    public function saveDiseaseModel(DiseaseModel $diseaseModel): void
    {
        DB::transaction(static function () use ($diseaseModel): void {
            $diseaseModel->save();
        });
    }

    public function deleteDiseaseModel(DiseaseModel $diseaseModel): void
    {
        DB::transaction(static function () use ($diseaseModel): void {
            $diseaseModel->delete();
        });
    }

    public function publishDiseaseModel(DiseaseModel $diseaseModel): void
    {
        DB::transaction(static function () use ($diseaseModel): void {
            $diseaseModel->publish();
        });
    }

    public function archiveDiseaseModel(DiseaseModel $diseaseModel): void
    {
        DB::transaction(static function () use ($diseaseModel): void {
            $diseaseModel->archive();
        });
    }
}
