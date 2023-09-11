<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\UpdatePlaceCounters;
use App\Models\Eloquent\Context;

class ContextObserver
{
    public function saved(Context $context): void
    {
        $this->updatePlaceCounters($context);
    }

    public function deleted(Context $context): void
    {
        $this->updatePlaceCounters($context);
    }

    private function updatePlaceCounters(Context $context): void
    {
        $place = $context->place;

        if ($place === null) {
            return;
        }

        UpdatePlaceCounters::dispatch($place->uuid);
    }
}
