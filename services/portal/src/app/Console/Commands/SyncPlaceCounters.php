<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\UpdatePlaceCounters;
use App\Repositories\PlaceRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class SyncPlaceCounters extends Command
{
    protected const CHUNK_COUNT = 100;

    protected $signature = 'place-counters:sync';

    protected $description = 'Sync the counters for places';

    public function handle(
        LoggerInterface $logger,
        PlaceRepository $placeRepository,
    ): int {
        $logger->info('Making jobs for updating place counters');

        $placeRepository->chunkPlaces(self::CHUNK_COUNT, static function (Collection $places): void {
            /** @var array<int, string> $placeUuids */
            $placeUuids = $places->pluck('uuid')->toArray();

            foreach ($placeUuids as $placeUuid) {
                UpdatePlaceCounters::dispatch($placeUuid)
                    ->afterCommit();
            }
        });

        $logger->info('Finished making jobs for updating place counters');

        return Command::SUCCESS;
    }
}
