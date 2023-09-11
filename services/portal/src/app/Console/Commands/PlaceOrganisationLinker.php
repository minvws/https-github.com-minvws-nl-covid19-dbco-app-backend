<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Eloquent\Place;
use App\Services\PlaceService;
use Illuminate\Console\Command;

use function app;

class PlaceOrganisationLinker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'place:link-organisations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Links places to an organisation based on the zipcode';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var PlaceService $placeService */
        $placeService = app(PlaceService::class);

        $bar = $this->output->createProgressBar(Place::query()->count());

        $this->output->writeln('Linking organisations to places..');

        $bar->start();
        Place::chunkById(100, static function ($places) use ($placeService, $bar): void {
            foreach ($places as $place) {
                if ($place instanceof Place && !empty($place->postalcode)) {
                    $place->organisation_uuid = $placeService->determineOrganisationUuid($place, $place->postalcode);
                    $place->save();
                    $bar->advance();
                }
            }
        }, 'uuid');

        $bar->finish();
        $this->output->newLine();

        return Command::SUCCESS;
    }
}
