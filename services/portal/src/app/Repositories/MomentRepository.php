<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Moment;
use App\Models\Eloquent\Place;
use Illuminate\Support\Collection;

interface MomentRepository
{
    /**
     * @return Collection<Moment>
     */
    public function getAllMomentsByContext(string $contextUuid): Collection;

    public function deleteAllMomentsByContext(string $contextUuid): void;

    public function getLastIndexPresenceByPlace(Place $place, ?string $lastIndexPresenceDateLimit = null): ?string;
}
