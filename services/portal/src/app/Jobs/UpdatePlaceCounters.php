<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\Config;
use App\Services\PlaceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Psr\Log\LoggerInterface;

class UpdatePlaceCounters implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly string $placeUuid,
    ) {
        $this->onConnection(Config::string('misc.place.counters.queue.connection'));
        $this->onQueue(Config::string('misc.place.counters.queue.queue_name'));
    }

    public function uniqueId(): string
    {
        return $this->placeUuid;
    }

    public function handle(PlaceService $placeService, LoggerInterface $logger): void
    {
        try {
            $result = $placeService->calculatePlaceCounters($this->placeUuid);
            $logger->debug('Counters have been updated', [
                'placeUuid' => $this->placeUuid,
                'result' => $result,
            ]);
        } catch (ModelNotFoundException) {
            $logger->debug('Place not found', [
                'placeUuid' => $this->placeUuid,
            ]);
        }
    }
}
