<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Moment;
use App\Models\Eloquent\Place;
use Illuminate\Support\Collection;

class DbMomentRepository implements MomentRepository
{
    /**
     * @return Collection<Moment>
     */
    public function getAllMomentsByContext(string $contextUuid): Collection
    {
        return Moment::where('context_uuid', $contextUuid)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    public function deleteAllMomentsByContext(string $contextUuid): void
    {
        Moment::where('context_uuid', $contextUuid)->delete();
    }

    public function getLastIndexPresenceByPlace(Place $place, ?string $lastIndexPresenceDateLimit = null): ?string
    {
        /** @var ?string $lastIndexPresence */
        $lastIndexPresence = Moment::query()
            ->join('context', 'context.uuid', '=', 'moment.context_uuid')
            ->where('context.place_uuid', $place->uuid)
            ->where('moment.day', '>', $lastIndexPresenceDateLimit)
            ->max('moment.day');

        return $lastIndexPresence;
    }
}
