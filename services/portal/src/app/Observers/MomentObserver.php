<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\UpdatePlaceCounters;
use App\Models\Eloquent\Moment;

class MomentObserver
{
    public function created(Moment $moment): void
    {
        $this->updatePlaceCounters($moment);
    }

    public function deleted(Moment $moment): void
    {
        $this->updatePlaceCounters($moment);
    }

    private function updatePlaceCounters(Moment $moment): void
    {
        $place = $moment->context->place;

        if ($place === null) {
            return;
        }

        UpdatePlaceCounters::dispatch($place->uuid);
    }
}
