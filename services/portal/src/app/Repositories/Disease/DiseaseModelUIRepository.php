<?php

declare(strict_types=1);

namespace App\Repositories\Disease;

use App\Models\Disease\DiseaseModel;
use App\Models\Disease\DiseaseModelUI;
use App\Models\Disease\VersionStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiseaseModelUIRepository
{
    public function getAllDiseaseModelUIs(DiseaseModel $diseaseModel): Collection
    {
        return $diseaseModel->uis()->orderBy('version', 'desc')->get();
    }

    public function getDiseaseModelUI(int $id): ?DiseaseModelUI
    {
        return DiseaseModelUI::query()->find($id);
    }

    public function firstDiseaseModelUIWithStatus(DiseaseModel $diseaseModel, VersionStatus $status): ?DiseaseModelUI
    {
        return $diseaseModel->uis()->where('status', '=', $status)->with('diseaseModel')->first();
    }

    public function firstDiseaseModelUIWithVersion(DiseaseModel $diseaseModel, int $version): ?DiseaseModelUI
    {
        return $diseaseModel->uis()->where('version', '=', $version)->with('diseaseModel')->first();
    }

    public function makeDiseaseModelUI(DiseaseModel $diseaseModel): DiseaseModelUI
    {
        $diseaseModelUI = new DiseaseModelUI();
        $diseaseModelUI->diseaseModel()->associate($diseaseModel);
        return $diseaseModelUI;
    }

    public function saveDiseaseModelUI(DiseaseModelUI $diseaseModelUI): void
    {
        DB::transaction(static function () use ($diseaseModelUI): void {
            $diseaseModelUI->save();
        });
    }

    public function deleteDiseaseModelUI(DiseaseModelUI $diseaseModelUI): void
    {
        DB::transaction(static function () use ($diseaseModelUI): void {
            $diseaseModelUI->delete();
        });
    }

    public function publishDiseaseModelUI(DiseaseModelUI $diseaseModelUI): void
    {
        DB::transaction(static function () use ($diseaseModelUI): void {
            $diseaseModelUI->publish();
        });
    }

    public function archiveDiseaseModelUI(DiseaseModelUI $diseaseModelUI): void
    {
        DB::transaction(static function () use ($diseaseModelUI): void {
            $diseaseModelUI->archive();
        });
    }
}
