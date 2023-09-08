<?php

declare(strict_types=1);

namespace App\Repositories\Disease;

use App\Models\Disease\Disease;
use App\Models\Disease\VersionStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiseaseRepository
{
    public function getAllDiseases(): Collection
    {
        $currentVersion = "
            disease.*,
            (
                SELECT m.version
                FROM disease_model m
                WHERE m.disease_id = disease.id
                AND status = 'published'
                LIMIT 1
            ) AS current_version
        ";

        return Disease::query()->orderBy('name')->selectRaw($currentVersion)->get();
    }

    public function getActiveDiseases(): Collection
    {
        return Disease::query()
            ->whereExists(static function (Builder $query): void {
                $query->select(DB::raw(1))
                    ->from('disease_model')
                    ->whereColumn('disease_model.disease_id', '=', 'disease.id')
                    ->where('disease_model.status', '=', VersionStatus::Published);
            })
            ->orderBy('name')
            ->get();
    }

    public function getDisease(int $id): ?Disease
    {
        return Disease::query()->find($id);
    }

    public function makeDisease(): Disease
    {
        return new Disease();
    }

    public function saveDisease(Disease $disease): void
    {
        DB::transaction(static function () use ($disease): void {
            $disease->save();
        });
    }

    public function deleteDisease(Disease $disease): void
    {
        DB::transaction(static function () use ($disease): void {
            $disease->delete();
        });
    }
}
