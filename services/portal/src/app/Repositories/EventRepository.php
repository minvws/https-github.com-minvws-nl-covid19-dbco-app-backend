<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Event;
use App\Models\Export\Cursor;
use App\Services\Export\Helpers\ExportFetchMutationsHelper;
use Illuminate\Support\Collection;

class EventRepository
{
    public function __construct(private readonly ExportFetchMutationsHelper $fetchMutationsHelper)
    {
    }

    public function getByUuid(string $value): ?Event
    {
        return Event::where('uuid', $value)->first();
    }

    public function getMutatedEventsForOrganisations(Collection $organisationIds, Cursor $cursor, int $limit): Collection
    {
        return $this->fetchMutationsHelper->fetchMutations(
            'event',
            'i_event_mutation',
            'created_at',
            null,
            $organisationIds,
            $cursor,
            $limit,
        );
    }
}
