<?php

declare(strict_types=1);

namespace App\Repositories\Dossier;

use App\Models\Dossier\Dossier;
use App\Models\Dossier\Event;
use Illuminate\Support\Facades\DB;

class EventRepository
{
    public function getEvent(int $id): ?Event
    {
        return Event::query()->find($id);
    }

    public function makeEvent(Dossier $dossier): Event
    {
        $event = new Event();
        $event->dossier()->associate($dossier);
        return $event;
    }

    public function saveEvent(Event $event): void
    {
        DB::transaction(static function () use ($event): void {
            $event->save();
        });
    }
}
